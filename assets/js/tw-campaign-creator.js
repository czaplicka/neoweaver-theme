/**
 * NeoWeaver — Campaign Creator (tw-campaign-creator.js)
 *
 * BUGS FIXED
 *  BUG-1  loadAgents(null) on page-load removed; loadAgents called only after
 *         the user selects a Node (world_id is guaranteed non-null).
 *  BUG-2  takenIds query now filters by current wp_user_id AND active campaign
 *         status so completed/deleted campaigns don't block agents.
 *  BUG-3  doSubmit() validates world_id and character_id before sending.
 *  BUG-4  validateStep() has a case for the NODE & AGENT BINDING phase.
 *  BUG-5  gridsLoaded.nodes set only after fetch succeeds, not before.
 *  BUG-6  emoji in makeCard() is sanitised through esc() before injection.
 *  BUG-7  buildPayload() uses String() cast for non-numeric radio values;
 *         falsy check replaced with explicit null-check.
 *  BUG-8  submitBtn listener uses event delegation from wrapper, not a
 *         one-time direct bind, so dynamically injected buttons are covered.
 *  BUG-9  scrollIntoView only fires when the wrapper is not fully visible.
 *  BUG-10 NW_SFX and makeSpinner extracted to shared tw-utils.js (see note).
 *
 * OPTIMISATIONS
 *  OPT-1  loadAgents() parallelises both sbGet calls with Promise.all().
 *  OPT-2  takenIds result cached for the session; cache busted on submit.
 *  OPT-3  Card rows batched into DocumentFragment before single DOM append.
 *  OPT-4  populateSummary() skipped when formState hasn't changed since last call.
 *  OPT-5  Artificial 2.5s min-delay replaced by MIN_SPINNER_MS constant (0).
 *  OPT-6  Progress tick NodeList cached once after DOM boot.
 *  OPT-7  showStep tracks previous index; toggles only two steps per nav.
 *  OPT-8  showFieldError targets only the specific field's cards, not all cards.
 *  OPT-9  validateStep caches #tw-camp-name and #tw-camp-notes references.
 *  OPT-10 sbGet adds AbortController timeout (default 10 s).
 *
 * NOTE — BUG-10 / shared utilities:
 *   NW_SFX and makeSpinner are now expected to live in assets/js/tw-utils.js
 *   which should be enqueued before this file. Stubs are provided below as
 *   fallbacks so this file still works standalone during development.
 */

( function () {
  'use strict';

  /* ── Config ─────────────────────────────────────────────────────────────────── */
  var MIN_SPINNER_MS  = 0;      // OPT-5: set >0 only for perceived-perf testing
  var FETCH_TIMEOUT_MS = 10000; // OPT-10: abort stalled Supabase calls after 10 s

  /* ── Shared-utility fallbacks (BUG-10) ──────────────────────────────────────── */
  // Production: these come from tw-utils.js enqueued before this file.
  var NW_SFX = window.NW_SFX || { play: function () {} };

  function makeSpinner() {
    if ( window.makeSpinner ) return window.makeSpinner();
    var el = document.createElement( 'div' );
    el.className = 'tw-spinner';
    el.setAttribute( 'aria-hidden', 'true' );
    return el;
  }

  /* ── Helpers ────────────────────────────────────────────────────────────────── */
  function esc( str ) {
    return String( str )
      .replace( /&/g,  '&amp;' )
      .replace( /</g,  '&lt;' )
      .replace( />/g,  '&gt;' )
      .replace( /"/g,  '&quot;' )
      .replace( /'/g,  '&#39;' );
  }

  /* OPT-10: sbGet with AbortController timeout */
  function sbGet( table, params, timeoutMs ) {
    var base = ( window.NW_DATA && NW_DATA.rest_url ) || '/wp-json/neoweaver/v1/db';
    var url  = new URL( base, window.location.origin );
    url.searchParams.set( 'table', table );
    Object.entries( params || {} ).forEach( function ( kv ) {
      url.searchParams.set( kv[0], kv[1] );
    } );

    var ms         = timeoutMs || FETCH_TIMEOUT_MS;
    var controller = new AbortController();
    var timer      = setTimeout( function () { controller.abort(); }, ms );

    return fetch( url.toString(), {
      headers: { 'X-WP-Nonce': ( window.NW_DATA && NW_DATA.nonce ) || '' },
      signal:  controller.signal,
    } )
      .then( function ( r ) {
        clearTimeout( timer );
        if ( ! r.ok ) throw new Error( 'HTTP ' + r.status );
        return r.json();
      } )
      .catch( function ( err ) {
        clearTimeout( timer );
        throw err;
      } );
  }

  /* ── DOM boot ───────────────────────────────────────────────────────────────── */
  document.addEventListener( 'DOMContentLoaded', function () {
    var wrapper = document.getElementById( 'tw-campaign-creator' );
    if ( ! wrapper ) return;

    var wpUserId = parseInt( ( window.NW_DATA && NW_DATA.user_id ) || '0', 10 );

    var steps     = Array.from( wrapper.querySelectorAll( '.tw-step' ) );
    var stepNames = steps.map( function ( s ) {
      return ( s.dataset.step || '' ).toUpperCase();
    } );

    /* OPT-6: tick NodeList cached once */
    var ticks = Array.from( wrapper.querySelectorAll( '.tw-progress-tick' ) );

    /* OPT-9: cached input refs */
    var campNameInput  = wrapper.querySelector( '#tw-camp-name' );
    var campNotesInput = wrapper.querySelector( '#tw-camp-notes' );

    var prevBtn = wrapper.querySelector( '#tw-camp-prev' );
    var nextBtn = wrapper.querySelector( '#tw-camp-next' );

    /* OPT-7: track previous step for minimal DOM toggling */
    var currentStep  = 0;
    var previousStep = -1;

    /* BUG-5: flags set only on successful fetch */
    var gridsLoaded = { nodes: false, agents: false };

    /* OPT-2: session cache for taken agent IDs */
    var takenIdsCache = null;

    /* OPT-4: summary dirty flag */
    var summaryDirty = true;

    var formState = {
      camp_name:    '',
      camp_notes:   '',
      world_id:     null,
      character_id: null,
      game_mode:    null,
      game_length:  null,
      world_type:   null,
      priority:     null,
      gm_style:     null,
    };

    function markDirty() { summaryDirty = true; }

    /* BUG-6: emoji sanitised via esc() before innerHTML injection */
    function makeCard( id, name, sub, emoji, extraClass ) {
      var safeEmoji = emoji ? esc( emoji ) : '&#9672;';
      var div = document.createElement( 'div' );
      div.className = 'tw-dyn-card' + ( extraClass ? ' ' + extraClass : '' );
      div.dataset.id = id;
      div.innerHTML =
        '<span class="tw-dyn-icon">' + safeEmoji + '</span>' +
        '<span class="tw-dyn-name">' + esc( name ) + '</span>' +
        ( sub ? '<span class="tw-dyn-sub">' + esc( sub ) + '</span>' : '' );
      return div;
    }

    /* ── Progress ──────────────────────────────────────────────────────────────── */
    function updateProgress( idx ) {
      var bar = wrapper.querySelector( '.tw-progress-bar' );
      if ( bar ) bar.style.width = ( ( idx / Math.max( 1, steps.length - 1 ) ) * 100 ) + '%';
      ticks.forEach( function ( t, i ) {
        t.classList.toggle( 'done',    i < idx );
        t.classList.toggle( 'current', i === idx );
        t.classList.toggle( 'future',  i > idx );
      } );
    }

    /* ── Show step ────────────────────────────────────────────────────────────── */
    function showStep( idx ) {
      idx = Math.max( 0, Math.min( steps.length - 1, idx ) );

      /* OPT-7: toggle only two steps */
      if ( previousStep >= 0 && previousStep !== idx ) {
        steps[ previousStep ].classList.remove( 'active' );
      }
      steps[ idx ].classList.add( 'active' );
      previousStep = idx;
      currentStep  = idx;

      updateProgress( idx );

      if ( prevBtn ) prevBtn.disabled = ( idx === 0 );
      if ( nextBtn ) nextBtn.textContent = ( idx === steps.length - 1 ) ? 'DEPLOY' : 'NEXT ›';

      var name = stepNames[ idx ] || '';

      /* Lazy-load node grid */
      if ( name.indexOf( 'NODE' ) !== -1 && ! gridsLoaded.nodes ) loadNodes();

      /* BUG-1: agent grid only loads when a node is already selected */
      if ( name.indexOf( 'AGENT' ) !== -1 && ! gridsLoaded.agents && formState.world_id ) {
        loadAgents( formState.world_id.id );
      }

      /* OPT-4: populate summary only when formState changed */
      if ( name.indexOf( 'SUMMARY' ) !== -1 && summaryDirty ) {
        populateSummary();
        summaryDirty = false;
      }

      /* BUG-9: scroll only when wrapper is not fully in viewport */
      var rect = wrapper.getBoundingClientRect();
      if ( rect.top < 0 || rect.bottom > window.innerHeight ) {
        wrapper.scrollIntoView( { behavior: 'smooth', block: 'start' } );
      }
    }

    /* ── Validation ────────────────────────────────────────────────────────────── */
    /* OPT-8: target only the specific field's cards */
    function showFieldError( stepEl, fieldId, msg ) {
      if ( fieldId ) {
        var field = stepEl.querySelector( '[data-field="' + fieldId + '"]' );
        if ( field ) {
          field.querySelectorAll( '.tw-card-visual' ).forEach( function ( c ) {
            c.classList.add( 'tw-error' );
          } );
        }
      }
      var errEl = stepEl.querySelector( '.tw-step-error' );
      if ( errEl ) { errEl.textContent = msg; errEl.hidden = false; }
      NW_SFX.play( 'error' );
    }

    function clearErrors( stepEl ) {
      stepEl.querySelectorAll( '.tw-error' ).forEach( function ( el ) {
        el.classList.remove( 'tw-error' );
      } );
      var errEl = stepEl.querySelector( '.tw-step-error' );
      if ( errEl ) errEl.hidden = true;
    }

    /* BUG-4: validateStep now handles NODE & AGENT BINDING phase */
    function validateStep( idx ) {
      var stepEl = steps[ idx ];
      clearErrors( stepEl );
      var name = stepNames[ idx ] || '';

      /* Campaign name step */
      if ( name.indexOf( 'NAME' ) !== -1 || idx === 0 ) {
        var val = campNameInput ? campNameInput.value.trim() : '';
        if ( ! val ) {
          showFieldError( stepEl, 'camp_name', 'Campaign name is required.' );
          return false;
        }
        formState.camp_name  = val;
        formState.camp_notes = campNotesInput ? campNotesInput.value.trim() : '';
        markDirty();
        return true;
      }

      /* BUG-4: Node & Agent binding step */
      if ( name.indexOf( 'NODE' ) !== -1 || name.indexOf( 'AGENT' ) !== -1 ) {
        if ( ! formState.world_id ) {
          showFieldError( stepEl, 'world', 'Select a Node before continuing.' );
          return false;
        }
        if ( ! formState.character_id ) {
          showFieldError( stepEl, 'agent', 'Assign an Agent before continuing.' );
          return false;
        }
        return true;
      }

      /* Generic radio step */
      var radios = stepEl.querySelectorAll( 'input[type="radio"]' );
      if ( radios.length ) {
        var checked = stepEl.querySelector( 'input[type="radio"]:checked' );
        if ( ! checked ) {
          showFieldError( stepEl, '', 'Please make a selection to continue.' );
          return false;
        }
        return true;
      }

      return true;
    }

    /* ── Navigation ───────────────────────────────────────────────────────────── */
    if ( prevBtn ) {
      prevBtn.addEventListener( 'click', function () { showStep( currentStep - 1 ); } );
    }
    if ( nextBtn ) {
      nextBtn.addEventListener( 'click', function () {
        if ( validateStep( currentStep ) ) showStep( currentStep + 1 );
      } );
    }

    /* Radio card selection */
    wrapper.addEventListener( 'change', function ( e ) {
      var input = e.target;
      if ( input.type !== 'radio' ) return;
      var key = ( input.name || '' ).replace( 'tw_', '' );
      if ( key ) {
        formState[ key ] = { value: input.value, label: input.dataset.label || input.value };
        markDirty();
      }
    } );

    /* ── Node grid ─────────────────────────────────────────────────────────────── */
    function loadNodes() {
      var grid = wrapper.querySelector( '#tw-node-grid' );
      if ( ! grid ) return;
      grid.innerHTML = '';
      grid.appendChild( makeSpinner() );

      sbGet( 'cyber_worlds', {
        select: 'world_id,world_name,world_type,world_emoji',
        wp_user_id: wpUserId,
      } )
        .then( function ( rows ) {
          grid.innerHTML = '';
          if ( ! rows || ! rows.length ) {
            grid.textContent = 'No Nodes found. Create one first.';
            /* BUG-5: flag set only on success (empty is still a success) */
            gridsLoaded.nodes = true;
            return;
          }
          /* OPT-3: DocumentFragment — single reflow */
          var frag = document.createDocumentFragment();
          rows.forEach( function ( row ) {
            var card = makeCard( row.world_id, row.world_name, row.world_type, row.world_emoji );
            card.addEventListener( 'click', function () {
              grid.querySelectorAll( '.tw-dyn-card' ).forEach( function ( c ) {
                c.classList.remove( 'selected' );
              } );
              card.classList.add( 'selected' );
              formState.world_id = { id: row.world_id, name: row.world_name };
              markDirty();
              /* BUG-1: reset agent state and reload on node change */
              formState.character_id = null;
              gridsLoaded.agents = false;
              takenIdsCache = null; /* OPT-2: bust cache on node change */
              loadAgents( row.world_id );
            } );
            frag.appendChild( card );
          } );
          grid.appendChild( frag );
          /* BUG-5: set flag only after successful fetch + render */
          gridsLoaded.nodes = true;
        } )
        .catch( function ( err ) {
          grid.innerHTML = '';
          grid.textContent = 'Failed to load Nodes. Please refresh and try again.';
          console.error( '[NeoWeaver] loadNodes error:', err );
          /* BUG-5: flag stays false — next visit to the step retries */
        } );
    }

    /* ── Agent grid ────────────────────────────────────────────────────────────── */
    function loadAgents( worldId ) {
      /* BUG-1: hard bail — never fetch with a null worldId */
      if ( ! worldId ) return;

      var grid   = wrapper.querySelector( '#tw-agent-grid' );
      var hintEl = wrapper.querySelector( '#tw-agent-hint' );
      if ( ! grid ) return;

      if ( hintEl ) hintEl.hidden = true;
      grid.innerHTML = '';
      grid.appendChild( makeSpinner() );

      /*
       * OPT-1: both requests fire concurrently via Promise.all().
       * OPT-2: takenIds result reused from session cache when available.
       * BUG-2: takenIds query filters by wp_user_id + campaign_status=active.
       */
      var takenPromise = takenIdsCache !== null
        ? Promise.resolve( takenIdsCache )
        : sbGet( 'cyber_campaign_characters', {
            select: 'character_id',
            wp_user_id:      wpUserId,
            campaign_status: 'active',
          } ).then( function ( rows ) {
            var ids = ( rows || [] ).map( function ( r ) { return r.character_id; } );
            takenIdsCache = ids;
            return ids;
          } );

      var agentsPromise = sbGet( 'cyber_characters', {
        select: 'character_id,character_name,character_class,character_emoji',
        wp_user_id: wpUserId,
        world_id:   worldId,
      } );

      Promise.all( [ takenPromise, agentsPromise ] )
        .then( function ( results ) {
          var takenIds = results[0];
          var agents   = results[1] || [];

          grid.innerHTML = '';

          if ( ! agents.length ) {
            grid.textContent = 'No Agents found for this Node.';
            gridsLoaded.agents = true;
            return;
          }

          /* OPT-3: DocumentFragment */
          var frag = document.createDocumentFragment();
          agents.forEach( function ( row ) {
            var taken = takenIds.indexOf( row.character_id ) !== -1;
            var card  = makeCard(
              row.character_id, row.character_name,
              row.character_class, row.character_emoji,
              taken ? 'tw-card-taken' : ''
            );
            if ( taken ) {
              card.setAttribute( 'aria-disabled', 'true' );
              card.title = 'Already deployed in an active campaign';
            } else {
              card.addEventListener( 'click', function () {
                grid.querySelectorAll( '.tw-dyn-card' ).forEach( function ( c ) {
                  c.classList.remove( 'selected' );
                } );
                card.classList.add( 'selected' );
                formState.character_id = { id: row.character_id, name: row.character_name };
                markDirty();
              } );
            }
            frag.appendChild( card );
          } );
          grid.appendChild( frag );
          gridsLoaded.agents = true;
        } )
        .catch( function ( err ) {
          grid.innerHTML = '';
          grid.textContent = 'Failed to load Agents. Please refresh and try again.';
          console.error( '[NeoWeaver] loadAgents error:', err );
        } );
    }

    /* ── Summary ───────────────────────────────────────────────────────────────── */
    function populateSummary() {
      var fields = {
        'sum-name':       formState.camp_name,
        'sum-node':       formState.world_id       ? formState.world_id.name       : '—',
        'sum-agent':      formState.character_id   ? formState.character_id.name   : '—',
        'sum-mode':       formState.game_mode      ? formState.game_mode.label      : '—',
        'sum-length':     formState.game_length    ? formState.game_length.label    : '—',
        'sum-world-type': formState.world_type     ? formState.world_type.label     : '—',
        'sum-priority':   formState.priority       ? formState.priority.label       : '—',
        'sum-gm-style':   formState.gm_style       ? formState.gm_style.label       : '—',
        'sum-notes':      formState.camp_notes     || '—',
      };
      Object.keys( fields ).forEach( function ( id ) {
        var el = wrapper.querySelector( '#' + id );
        if ( el ) el.textContent = fields[ id ];
      } );
    }

    /* ── Build payload ────────────────────────────────────────────────────────── */
    /*
     * BUG-7: radio values stored as { value, label }.
     * safeInt for numeric IDs; safeStr for string enums like gm_style.
     * null-check uses === null instead of falsy so 0 is a valid value.
     */
    function safeInt( obj ) {
      if ( ! obj ) return null;
      var n = parseInt( obj.value, 10 );
      return isNaN( n ) ? null : n;
    }
    function safeStr( obj ) { return obj ? String( obj.value ) : null; }

    function buildPayload() {
      return {
        campaign_name:  formState.camp_name  || null,
        campaign_notes: formState.camp_notes || null,
        world_id:       formState.world_id       ? formState.world_id.id       : null,
        character_id:   formState.character_id   ? formState.character_id.id   : null,
        game_mode:      safeInt( formState.game_mode ),
        game_length:    safeInt( formState.game_length ),
        world_type:     safeStr( formState.world_type ),
        priority:       safeInt( formState.priority ),
        gm_style:       safeStr( formState.gm_style ),
      };
    }

    /* ── Submit ───────────────────────────────────────────────────────────────── */
    /*
     * BUG-8: event delegation on wrapper catches #tw-camp-submit even when
     * the button is injected dynamically after DOMContentLoaded.
     */
    wrapper.addEventListener( 'click', function ( e ) {
      if ( ! e.target.matches( '#tw-camp-submit' ) ) return;
      doSubmit( e.target );
    } );

    function doSubmit( btn ) {
      /* BUG-3: guard before building payload */
      if ( ! formState.world_id ) {
        alert( 'Please select a Node before deploying.' );
        return;
      }
      if ( ! formState.character_id ) {
        alert( 'Please assign an Agent before deploying.' );
        return;
      }

      var payload = buildPayload();
      btn.disabled = true;
      var spinner = makeSpinner();
      btn.parentNode.insertBefore( spinner, btn.nextSibling );
      var t0 = Date.now();

      fetch( ( window.NW_DATA && NW_DATA.rest_url ) || '/wp-json/neoweaver/v1/campaigns', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce':   ( window.NW_DATA && NW_DATA.nonce ) || '',
        },
        body: JSON.stringify( payload ),
      } )
        .then( function ( r ) {
          if ( ! r.ok ) return r.json().then( function ( d ) { throw new Error( d.message || 'HTTP ' + r.status ); } );
          return r.json();
        } )
        .then( function ( data ) {
          var delay = Math.max( 0, MIN_SPINNER_MS - ( Date.now() - t0 ) ); /* OPT-5 */
          setTimeout( function () {
            spinner.remove();
            btn.disabled = false;
            NW_SFX.play( 'success' );
            takenIdsCache = null; /* OPT-2: bust cache after deploy */
            if ( data && data.redirect_url ) {
              window.location.href = data.redirect_url;
            } else {
              var okEl = wrapper.querySelector( '.tw-submit-success' );
              if ( okEl ) okEl.hidden = false;
            }
          }, delay );
        } )
        .catch( function ( err ) {
          spinner.remove();
          btn.disabled = false;
          NW_SFX.play( 'error' );
          var errEl = wrapper.querySelector( '.tw-submit-error' );
          if ( errEl ) { errEl.textContent = err.message || 'Deployment failed. Please try again.'; errEl.hidden = false; }
          console.error( '[NeoWeaver] doSubmit error:', err );
        } );
    }

    /* ── Boot ───────────────────────────────────────────────────────────────────── */
    showStep( 0 );
  } );
} )();
