/**
 * NeoWeaver – Campaign Creator
 * tw-campaign-creator.js
 *
 * Bug fixes applied:
 * 1. loadAgents(null) removed from loadNodes() resolve chain – agents only load on step 3 entry
 * 2. parseInt() removed from UUID/already-integer values; kept only where genuinely needed
 * 3. gridsLoaded.nodes set AFTER successful fetch, not before
 * 4. sbGet sanitises table name against an allowlist
 * 5. setStatus('', false) now also resets element colour/class
 * 6. submitBtn text reset uses textContent consistently with actual HTML content
 * 7. makeCard innerHTML uses esc() helper for dynamic values
 * 8. AbortController added to doSubmit fetch; previous controller aborted on re-submit
 */

/* global neoweaverCampaignCreator */
( function () {
    'use strict';

    // -----------------------------------------------------------------------
    // Config & state
    // -----------------------------------------------------------------------

    const cfg = window.neoweaverCampaignCreator || {};

    const formState = {
        worldId:       null,   // UUID string – never parsed through parseInt
        characterId:   null,   // UUID string
        campaignName:  '',
        gameMode:      null,   // integer (from radio value attr, safe to parse)
        gameLength:    null,
        worldType:     null,
        priority:      null,
    };

    // Fix 3: track loaded state per grid; only set true after successful fetch
    const gridsLoaded = {
        nodes:  false,
        agents: false,
    };

    // Fix 8: hold active AbortController so we can cancel orphaned submits
    let submitController = null;

    // -----------------------------------------------------------------------
    // DOM refs
    // -----------------------------------------------------------------------

    const container   = document.querySelector( '.neoweaver-campaign-creator' );
    if ( ! container ) return;

    const steps       = container.querySelectorAll( '.nw-form-step' );
    const stepDots    = container.querySelectorAll( '.nw-step' );
    const progressBar = container.querySelector( '.nw-progress__bar' );
    const prevBtn     = container.querySelector( '#nw-prev-btn' );
    const nextBtn     = container.querySelector( '#nw-next-btn' );
    const submitBtn   = container.querySelector( '#nw-deploy-btn' );
    const statusEl    = container.querySelector( '#nw-status' );
    const worldList   = container.querySelector( '#nw-world-list' );
    const agentList   = container.querySelector( '#nw-agent-list' );
    const summaryEl   = container.querySelector( '#nw-summary' );

    let currentStep = 1;
    const totalSteps = steps.length;

    // -----------------------------------------------------------------------
    // Fix 4: Supabase fetch – allowlisted table names only
    // -----------------------------------------------------------------------

    const ALLOWED_TABLES = new Set( [
        'cyber_nodes',
        'cyber_characters',
        'cyber_deployments',
        'cyber_tags',
    ] );

    /**
     * @param {string} table  – must be in ALLOWED_TABLES
     * @param {object} params – query string params
     * @returns {Promise<any[]>}
     */
    async function sbGet( table, params = {}, signal = null ) {
        if ( ! ALLOWED_TABLES.has( table ) ) {
            console.error( '[NeoWeaver] sbGet: unknown table', table );
            return [];
        }

        const url = new URL( cfg.supabaseUrl.replace( /\/$/, '' ) + '/rest/v1/' + table );
        Object.entries( params ).forEach( ( [ k, v ] ) => url.searchParams.set( k, v ) );

        const opts = {
            headers: {
                'apikey':        cfg.supabaseKey,
                'Authorization': 'Bearer ' + cfg.supabaseKey,
                'Content-Type':  'application/json',
            },
        };
        if ( signal ) opts.signal = signal;

        const res = await fetch( url.toString(), opts );
        if ( ! res.ok ) throw new Error( 'Supabase error ' + res.status );
        return res.json();
    }

    // -----------------------------------------------------------------------
    // Fix 5: setStatus with full style reset
    // -----------------------------------------------------------------------

    function setStatus( msg, isError = false ) {
        if ( ! statusEl ) return;
        statusEl.textContent = msg;
        // Reset colour each time – not just when msg is empty
        statusEl.classList.remove( 'nw-status--error', 'nw-status--ok' );
        if ( msg ) {
            statusEl.classList.add( isError ? 'nw-status--error' : 'nw-status--ok' );
        }
    }

    // -----------------------------------------------------------------------
    // Fix 7: escape helper used in makeCard for dynamic content
    // -----------------------------------------------------------------------

    function esc( str ) {
        return String( str )
            .replace( /&/g, '&amp;' )
            .replace( /</g, '&lt;' )
            .replace( />/g, '&gt;' )
            .replace( /"/g, '&quot;' )
            .replace( /'/g, '&#039;' );
    }

    function makeCard( { id, title, subtitle = '', meta = '', tags = [] } ) {
        const tagHtml = tags
            .map( t => `<span class="nw-tag">${ esc( t ) }</span>` )
            .join( '' );

        return `<div class="nw-card" data-id="${ esc( id ) }" role="button" tabindex="0"
                     aria-label="${ esc( title ) }">
            <div class="nw-card__title">${ esc( title ) }</div>
            ${ subtitle ? `<div class="nw-card__subtitle">${ esc( subtitle ) }</div>` : '' }
            ${ meta     ? `<div class="nw-card__meta">${ esc( meta ) }</div>`     : '' }
            ${ tagHtml  ? `<div class="nw-card__tags">${ tagHtml }</div>`          : '' }
        </div>`;
    }

    // -----------------------------------------------------------------------
    // Fix 1: loadNodes does NOT call loadAgents – agents load on step 3 entry
    // Fix 3: gridsLoaded.nodes set only after successful fetch
    // -----------------------------------------------------------------------

    async function loadNodes() {
        if ( gridsLoaded.nodes ) return;

        worldList.innerHTML = '<div class="nw-skeleton"></div>';

        try {
            const worldId = container.dataset.worldId || null;
            const params  = { select: 'id,name,description,world_type,tags', order: 'name.asc' };
            if ( worldId ) params[ 'id' ] = 'eq.' + worldId;

            const nodes = await sbGet( 'cyber_nodes', params );

            if ( ! nodes.length ) {
                worldList.innerHTML = '<p class="nw-empty">No worlds available.</p>';
                // Do NOT set gridsLoaded.nodes so a retry can reload
                return;
            }

            worldList.innerHTML = nodes.map( n => makeCard( {
                id:       n.id,
                title:    n.name,
                subtitle: n.world_type || '',
                meta:     n.description || '',
                tags:     Array.isArray( n.tags ) ? n.tags : [],
            } ) ).join( '' );

            // Fix 3: only mark loaded AFTER successful render
            gridsLoaded.nodes = true;

            worldList.querySelectorAll( '.nw-card' ).forEach( card => {
                card.addEventListener( 'click', () => selectNode( card ) );
                card.addEventListener( 'keydown', e => {
                    if ( e.key === 'Enter' || e.key === ' ' ) selectNode( card );
                } );
            } );

        } catch ( err ) {
            console.error( '[NeoWeaver] loadNodes error', err );
            worldList.innerHTML = '<p class="nw-error">Failed to load worlds. Please try again.</p>';
            // gridsLoaded.nodes remains false → retry works
        }
        // Note: loadAgents intentionally NOT called here (Fix 1)
    }

    // -----------------------------------------------------------------------
    // Fix 1: loadAgents only called when entering step 3, with world filter
    // Fix 3: gridsLoaded.agents set only after successful fetch
    // -----------------------------------------------------------------------

    async function loadAgents() {
        if ( gridsLoaded.agents ) return;

        agentList.innerHTML = '<div class="nw-skeleton"></div>';

        if ( ! formState.worldId ) {
            agentList.innerHTML = '<p class="nw-error">Select a world first.</p>';
            return;
        }

        try {
            // Filter characters to the selected world (Fix 1: always has world filter)
            const agents = await sbGet( 'cyber_characters', {
                select:   'id,name,class,level,description,tags',
                world_id: 'eq.' + formState.worldId,
                order:    'name.asc',
            } );

            if ( ! agents.length ) {
                agentList.innerHTML = '<p class="nw-empty">No field agents for this world.</p>';
                return;
            }

            agentList.innerHTML = agents.map( a => makeCard( {
                id:       a.id,
                title:    a.name,
                subtitle: a.class ? 'Class: ' + a.class + ( a.level ? ' \u00b7 Lv ' + a.level : '' ) : '',
                meta:     a.description || '',
                tags:     Array.isArray( a.tags ) ? a.tags : [],
            } ) ).join( '' );

            // Fix 3: only mark loaded after success
            gridsLoaded.agents = true;

            agentList.querySelectorAll( '.nw-card' ).forEach( card => {
                card.addEventListener( 'click', () => selectAgent( card ) );
                card.addEventListener( 'keydown', e => {
                    if ( e.key === 'Enter' || e.key === ' ' ) selectAgent( card );
                } );
            } );

        } catch ( err ) {
            console.error( '[NeoWeaver] loadAgents error', err );
            agentList.innerHTML = '<p class="nw-error">Failed to load agents. Please try again.</p>';
        }
    }

    // -----------------------------------------------------------------------
    // Selection helpers
    // -----------------------------------------------------------------------

    function selectNode( card ) {
        worldList.querySelectorAll( '.nw-card' ).forEach( c => c.classList.remove( 'nw-card--selected' ) );
        card.classList.add( 'nw-card--selected' );
        formState.worldId = card.dataset.id; // UUID – never parseInt'd

        // When world changes, reset agent selection and force agent grid reload
        formState.characterId  = null;
        gridsLoaded.agents     = false;
        agentList.innerHTML    = '<div class="nw-skeleton"></div>';

        setStatus( '' );
        nextBtn.disabled = false;
    }

    function selectAgent( card ) {
        agentList.querySelectorAll( '.nw-card' ).forEach( c => c.classList.remove( 'nw-card--selected' ) );
        card.classList.add( 'nw-card--selected' );
        formState.characterId = card.dataset.id; // UUID – never parseInt'd
        setStatus( '' );
    }

    // -----------------------------------------------------------------------
    // Fix 2: buildPayload – integer fields handled correctly
    // -----------------------------------------------------------------------

    function buildPayload() {
        const nameInput = container.querySelector( '#nw-campaign-name' );

        return {
            world_id:      formState.worldId,       // UUID string – NOT parseInt'd
            character_id:  formState.characterId,   // UUID string – NOT parseInt'd
            name:          ( nameInput ? nameInput.value : '' ).trim(),
            // radio .value attributes give strings; parseInt is correct here
            game_mode:     formState.gameMode     !== null ? parseInt( formState.gameMode,  10 ) : null,
            game_length:   formState.gameLength   !== null ? parseInt( formState.gameLength, 10 ) : null,
            world_type:    formState.worldType    !== null ? parseInt( formState.worldType,  10 ) : null,
            priority:      formState.priority     !== null ? parseInt( formState.priority,   10 ) : null,
        };
    }

    // -----------------------------------------------------------------------
    // Validation
    // -----------------------------------------------------------------------

    function validateStep( step ) {
        switch ( step ) {
            case 1: return !! formState.worldId;
            case 2: {
                const nameInput = container.querySelector( '#nw-campaign-name' );
                return nameInput && nameInput.value.trim().length >= 2;
            }
            case 3: return !! formState.characterId;
            default: return true;
        }
    }

    // -----------------------------------------------------------------------
    // Summary
    // -----------------------------------------------------------------------

    function buildSummary() {
        const payload = buildPayload();
        if ( ! summaryEl ) return;
        summaryEl.innerHTML = `
            <dl class="nw-summary-list">
                <dt>World</dt>    <dd>${ esc( payload.world_id     || '\u2013' ) }</dd>
                <dt>Campaign</dt> <dd>${ esc( payload.name         || '\u2013' ) }</dd>
                <dt>Agent</dt>    <dd>${ esc( payload.character_id || '\u2013' ) }</dd>
            </dl>`;
    }

    // -----------------------------------------------------------------------
    // Step navigation
    // -----------------------------------------------------------------------

    function goToStep( n ) {
        if ( n < 1 || n > totalSteps ) return;

        steps.forEach( ( s, i ) => s.classList.toggle( 'nw-form-step--active', i + 1 === n ) );
        stepDots.forEach( ( d, i ) => {
            d.classList.toggle( 'nw-step--active',    i + 1 === n );
            d.classList.toggle( 'nw-step--completed', i + 1 < n );
        } );

        const pct = Math.round( ( n / totalSteps ) * 100 );
        if ( progressBar ) {
            progressBar.style.width = pct + '%';
            progressBar.parentElement.setAttribute( 'aria-valuenow', pct );
        }

        prevBtn.disabled = n === 1;
        nextBtn.classList.toggle( 'nw-hidden', n === totalSteps );
        if ( submitBtn ) submitBtn.closest( '.nw-form-step' )?.classList.toggle( 'nw-form-step--active', n === totalSteps );

        currentStep = n;
        setStatus( '' );

        // Lazy-load grids on step entry (Fix 1: agents only loaded here)
        if ( n === 1 ) loadNodes();
        if ( n === 3 ) loadAgents();
        if ( n === totalSteps ) buildSummary();
    }

    // -----------------------------------------------------------------------
    // Fix 6: doSubmit – consistent button text reset + AbortController (Fix 8)
    // -----------------------------------------------------------------------

    async function doSubmit() {
        if ( ! validateStep( 1 ) || ! validateStep( 2 ) || ! validateStep( 3 ) ) {
            setStatus( 'Please complete all steps before deploying.', true );
            return;
        }

        // Fix 8: abort any in-flight submit before starting a new one
        if ( submitController ) {
            submitController.abort();
        }
        submitController = new AbortController();
        const { signal } = submitController;

        const payload = buildPayload();

        // Fix 6: read the original label from the DOM instead of a hardcoded string
        const originalLabel = submitBtn ? submitBtn.textContent : '';
        if ( submitBtn ) {
            submitBtn.disabled    = true;
            submitBtn.textContent = '\u23f3 Deploying\u2026';
        }

        setStatus( '' );

        try {
            const url = cfg.ajaxUrl;
            const body = new FormData();
            body.append( 'action', 'neoweaver_create_campaign' );
            body.append( 'nonce',  cfg.nonce );
            Object.entries( payload ).forEach( ( [ k, v ] ) => {
                if ( v !== null && v !== undefined ) body.append( k, v );
            } );

            const res = await fetch( url, { method: 'POST', body, signal } );

            if ( signal.aborted ) return; // user navigated away

            if ( ! res.ok ) throw new Error( 'Server error ' + res.status );
            const data = await res.json();

            if ( data.success ) {
                setStatus( '\u2705 Campaign deployed successfully!', false );
            } else {
                setStatus( '\u26a0\ufe0f ' + ( data.data?.message || 'Deployment failed.' ), true );
            }

        } catch ( err ) {
            if ( err.name === 'AbortError' ) return;
            console.error( '[NeoWeaver] doSubmit error', err );
            setStatus( '\u274c Connection error. Please try again.', true );
        } finally {
            if ( submitBtn && ! signal.aborted ) {
                submitBtn.disabled    = false;
                submitBtn.textContent = originalLabel;
            }
            submitController = null;
        }
    }

    // -----------------------------------------------------------------------
    // Radio inputs for game options (step 2 extras)
    // -----------------------------------------------------------------------

    container.querySelectorAll( 'input[type="radio"][name="game_mode"]' ).forEach( r =>
        r.addEventListener( 'change', () => { formState.gameMode = r.value; } ) );
    container.querySelectorAll( 'input[type="radio"][name="game_length"]' ).forEach( r =>
        r.addEventListener( 'change', () => { formState.gameLength = r.value; } ) );
    container.querySelectorAll( 'input[type="radio"][name="world_type"]' ).forEach( r =>
        r.addEventListener( 'change', () => { formState.worldType = r.value; } ) );
    container.querySelectorAll( 'input[type="radio"][name="priority"]' ).forEach( r =>
        r.addEventListener( 'change', () => { formState.priority = r.value; } ) );

    // -----------------------------------------------------------------------
    // Nav buttons
    // -----------------------------------------------------------------------

    prevBtn && prevBtn.addEventListener( 'click', () => goToStep( currentStep - 1 ) );

    nextBtn && nextBtn.addEventListener( 'click', () => {
        if ( ! validateStep( currentStep ) ) {
            setStatus( 'Complete this step before continuing.', true );
            return;
        }
        goToStep( currentStep + 1 );
    } );

    submitBtn && submitBtn.addEventListener( 'click', doSubmit );

    // -----------------------------------------------------------------------
    // Cleanup on page unload – abort any pending fetch (Fix 8)
    // -----------------------------------------------------------------------

    window.addEventListener( 'beforeunload', () => {
        if ( submitController ) submitController.abort();
    } );

    // -----------------------------------------------------------------------
    // Boot
    // -----------------------------------------------------------------------

    goToStep( 1 );

} )();
