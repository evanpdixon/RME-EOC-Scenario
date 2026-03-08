<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap rme-eoc-wrap">
    <h1>EOC Scenario Generator</h1>
    <p class="description">Generate location-customized student task cards and facilitator materials for radio emergency communications (emcom) training exercises.</p>

    <div id="rme-eoc-step-1" class="rme-eoc-section">
        <h2>Step 1: Location Setup</h2>

        <table class="form-table">
            <tr>
                <th><label for="rme-preset">Load Preset</label></th>
                <td>
                    <select id="rme-preset">
                        <option value="">-- Select a preset or enter manually --</option>
                        <option value="henderson_tx">Henderson, Texas</option>
                        <option value="stoneboro_pa">Stoneboro, Pennsylvania</option>
                        <option value="spiro_ok">Spiro, Oklahoma</option>
                        <option value="stony_point_nc">Stony Point, NC (HQ Default)</option>
                        <option value="jackson_oh">Jackson, Ohio</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="rme-venue">Venue Address</label></th>
                <td>
                    <input type="text" id="rme-venue" class="regular-text" placeholder="e.g. 200 N Mill St, Henderson, TX 75652">
                    <p class="description">Full street address for geocoding and location lookup.</p>
                </td>
            </tr>
            <tr>
                <th><label for="rme-location">Location Name</label></th>
                <td><input type="text" id="rme-location" class="regular-text" placeholder="e.g. Henderson, Texas"></td>
            </tr>
            <tr>
                <th><label for="rme-class-size">Class Size</label></th>
                <td><input type="number" id="rme-class-size" value="20" min="6" max="40" style="width:80px;"></td>
            </tr>
            <tr>
                <th><label for="rme-radius">Search Radius</label></th>
                <td>
                    <select id="rme-radius">
                        <option value="8000">8 km (~5 mi)</option>
                        <option value="16000" selected>16 km (~10 mi)</option>
                        <option value="32000">32 km (~20 mi)</option>
                        <option value="48000">48 km (~30 mi)</option>
                    </select>
                    <p class="description">How far to search for hospitals, gas stations, etc. Use larger radius for rural areas.</p>
                </td>
            </tr>
        </table>

        <div id="rme-lookup-controls">
            <p>
                <button id="rme-lookup-btn" class="button button-primary">Look Up Location</button>
                <button id="rme-skip-lookup-btn" class="button">Skip Lookup (Manual Entry)</button>
                <span id="rme-lookup-spinner" class="spinner" style="float:none;"></span>
            </p>
            <div id="rme-lookup-error" class="notice notice-error" style="display:none;"><p></p></div>
        </div>
    </div>

    <div id="rme-eoc-step-2" class="rme-eoc-section" style="display:none;">
        <h2>Step 2: Review &amp; Select</h2>

        <div id="rme-geocode-info" class="notice notice-info" style="display:none;"><p></p></div>
        <div id="rme-flood-info" class="notice notice-warning" style="display:none;"><p></p></div>

        <h3>Roads</h3>
        <table class="form-table">
            <tr>
                <th><label for="rme-main-road">Main Road</label></th>
                <td>
                    <select id="rme-main-road-select" class="rme-road-select"><option value="">-- Select or type below --</option></select>
                    <input type="text" id="rme-main-road" class="regular-text" placeholder="Residential road for neighbor addresses">
                    <p class="description">Used for all neighbor addresses in Tasks A/C/D.</p>
                </td>
            </tr>
            <tr>
                <th><label for="rme-flooded-road">Flooded Road</label></th>
                <td>
                    <select id="rme-flooded-road-select" class="rme-road-select"><option value="">-- Select or type below --</option></select>
                    <input type="text" id="rme-flooded-road" class="regular-text" placeholder="Road with flooding (Tasks E/L)">
                    <p class="description">Used in flooding contradiction between Tasks E and L.</p>
                </td>
            </tr>
            <tr>
                <th><label for="rme-blocked-road">Blocked Road</label></th>
                <td>
                    <select id="rme-blocked-road-select" class="rme-road-select"><option value="">-- Select or type below --</option></select>
                    <input type="text" id="rme-blocked-road" class="regular-text" placeholder="Road with downed lines (Tasks K/B/N)">
                    <p class="description">Used in downed power line scenario.</p>
                </td>
            </tr>
            <tr>
                <th><label for="rme-highway">Highway</label></th>
                <td>
                    <select id="rme-highway-select" class="rme-road-select"><option value="">-- Select or type below --</option></select>
                    <input type="text" id="rme-highway" class="regular-text" placeholder="e.g. US-79">
                </td>
            </tr>
            <tr>
                <th><label for="rme-main-st">Commercial Street</label></th>
                <td>
                    <select id="rme-main-st-select" class="rme-road-select"><option value="">-- Select or type below --</option></select>
                    <input type="text" id="rme-main-st" class="regular-text" placeholder="Local commercial corridor">
                </td>
            </tr>
        </table>

        <h3>Points of Interest</h3>
        <table class="form-table">
            <tr>
                <th><label for="rme-hospital">Hospital</label></th>
                <td>
                    <select id="rme-hospital-select" class="rme-poi-select"><option value="">-- Select or type below --</option></select>
                    <input type="text" id="rme-hospital" class="regular-text" placeholder="Nearest hospital with ER">
                </td>
            </tr>
            <tr>
                <th><label for="rme-gas-station">Gas Station</label></th>
                <td>
                    <select id="rme-gas-station-select" class="rme-poi-select"><option value="">-- Select or type below --</option></select>
                    <input type="text" id="rme-gas-station" class="regular-text" placeholder="e.g. Murphy USA on US-79 S">
                </td>
            </tr>
            <tr>
                <th><label for="rme-local-store">Farm/Supply Store</label></th>
                <td>
                    <select id="rme-local-store-select" class="rme-poi-select"><option value="">-- Select or type below --</option></select>
                    <input type="text" id="rme-local-store" class="regular-text" placeholder="e.g. Tractor Supply on US-79 S">
                </td>
            </tr>
        </table>

        <h3>Addresses &amp; People</h3>
        <p class="description">House numbers on the main road. These should be sequential and plausible for the area.</p>
        <table class="form-table">
            <tr>
                <th><label for="rme-addr-a">Task A Address #</label></th>
                <td><input type="text" id="rme-addr-a" style="width:100px;" placeholder="e.g. 412"></td>
            </tr>
            <tr>
                <th><label for="rme-addr-grandma">Grandmother Address #</label></th>
                <td><input type="text" id="rme-addr-grandma" style="width:100px;" placeholder="e.g. 416"></td>
            </tr>
            <tr>
                <th><label for="rme-addr-uncle">Uncle Address #</label></th>
                <td><input type="text" id="rme-addr-uncle" style="width:100px;" placeholder="e.g. 408"></td>
            </tr>
            <tr>
                <th><label for="rme-addr-neighbor">Neighbor Address #</label></th>
                <td><input type="text" id="rme-addr-neighbor" style="width:100px;" placeholder="e.g. 418"></td>
            </tr>
            <tr>
                <th><label for="rme-grandma-name">Grandmother Name</label></th>
                <td><input type="text" id="rme-grandma-name" class="regular-text" value="Hazel Pittman" readonly>
                <p class="description">Fixed character name used across all scenarios.</p></td>
            </tr>
            <tr>
                <th><label for="rme-uncle-name">Uncle Name</label></th>
                <td><input type="text" id="rme-uncle-name" class="regular-text" value="John" readonly>
                <p class="description">Fixed character name used across all scenarios.</p></td>
            </tr>
        </table>

        <h3>Output Options</h3>
        <table class="form-table">
            <tr>
                <th>Output Formats</th>
                <td>
                    <label><input type="checkbox" id="rme-format-pdf" value="pdf" checked> PDF (student cards + facilitator reference)</label><br>
                    <label><input type="checkbox" id="rme-format-txt" value="txt" checked> TXT (combined task list)</label>
                </td>
            </tr>
        </table>

        <p>
            <button id="rme-generate-btn" class="button button-primary button-hero">Generate Scenario</button>
            <button id="rme-back-btn" class="button">Back to Step 1</button>
            <span id="rme-generate-spinner" class="spinner" style="float:none;"></span>
        </p>
        <div id="rme-generate-error" class="notice notice-error" style="display:none;"><p></p></div>
    </div>

    <div id="rme-eoc-step-3" class="rme-eoc-section" style="display:none;">
        <h2>Step 3: Download</h2>
        <div id="rme-generate-summary" class="notice notice-success"><p></p></div>

        <div id="rme-download-links"></div>

        <p style="margin-top:20px;">
            <button id="rme-new-btn" class="button button-primary">Generate Another Scenario</button>
        </p>
    </div>
</div>
