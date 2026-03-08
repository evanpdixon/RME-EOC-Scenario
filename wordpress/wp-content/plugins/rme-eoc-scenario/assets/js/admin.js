(function($) {
    'use strict';

    // Field ID mappings (without 'rme-' prefix to config key)
    var configFields = {
        'location': 'location',
        'venue': 'venue',
        'main-road': 'main_road',
        'flooded-road': 'flooded_road',
        'blocked-road': 'blocked_road',
        'highway': 'highway',
        'main-st': 'main_st',
        'addr-a': 'addr_a',
        'addr-grandma': 'addr_grandma',
        'addr-uncle': 'addr_uncle',
        'addr-neighbor': 'addr_neighbor',
        'grandma-name': 'grandma_name',
        'uncle-name': 'uncle_name',
        'hospital': 'hospital',
        'gas-station': 'gas_station',
        'local-store': 'local_store'
    };

    // Load preset into form
    $('#rme-preset').on('change', function() {
        var key = $(this).val();
        if (!key || !rmeEoc.presets[key]) return;

        var preset = rmeEoc.presets[key];
        $('#rme-venue').val(preset.venue);
        $('#rme-location').val(preset.location);

        // Fill Step 2 fields
        $.each(configFields, function(fieldId, configKey) {
            if (preset[configKey]) {
                $('#rme-' + fieldId).val(preset[configKey]);
            }
        });

        // Show step 2 with preset data
        $('#rme-eoc-step-2').show();
    });

    // Dropdowns populate text inputs
    $(document).on('change', '.rme-road-select, .rme-poi-select', function() {
        var val = $(this).val();
        if (val) {
            $(this).next('input').val(val);
        }
    });

    // Look Up Location
    $('#rme-lookup-btn').on('click', function() {
        var address = $('#rme-venue').val().trim();
        if (!address) {
            showError('#rme-lookup-error', 'Please enter a venue address.');
            return;
        }

        var radius = $('#rme-radius').val();
        var $spinner = $('#rme-lookup-spinner');
        var $btn = $(this);

        $btn.prop('disabled', true);
        $spinner.addClass('is-active');
        hideError('#rme-lookup-error');

        $.post(rmeEoc.ajaxUrl, {
            action: 'rme_eoc_lookup',
            nonce: rmeEoc.nonce,
            address: address,
            radius: radius
        }, function(response) {
            $btn.prop('disabled', false);
            $spinner.removeClass('is-active');

            if (!response.success) {
                showError('#rme-lookup-error', response.data);
                return;
            }

            populateLookupResults(response.data);
            $('#rme-eoc-step-2').show();
            $('html, body').animate({ scrollTop: $('#rme-eoc-step-2').offset().top - 40 }, 300);
        }).fail(function() {
            $btn.prop('disabled', false);
            $spinner.removeClass('is-active');
            showError('#rme-lookup-error', 'Request failed. Check your connection.');
        });
    });

    // Skip Lookup
    $('#rme-skip-lookup-btn').on('click', function() {
        $('#rme-eoc-step-2').show();
        $('html, body').animate({ scrollTop: $('#rme-eoc-step-2').offset().top - 40 }, 300);
    });

    // Back button
    $('#rme-back-btn').on('click', function() {
        $('#rme-eoc-step-2').hide();
    });

    // Populate lookup results into dropdowns
    function populateLookupResults(data) {
        // Geocode info
        if (data.geocode) {
            $('#rme-geocode-info').show().find('p').text(
                'Geocoded: ' + data.geocode.display_name +
                ' (' + data.geocode.lat.toFixed(4) + ', ' + data.geocode.lon.toFixed(4) + ')'
            );
        }

        // Flood info
        if (data.flood && data.flood.has_flood_zones) {
            $('#rme-flood-info').show().find('p').text(
                'FEMA flood zones detected in this area: ' + data.flood.zone_types.join(', ') +
                '. Consider selecting a road near water crossings as the flooded road.'
            );
        }

        var nearby = data.nearby;

        // Populate road dropdowns
        populateSelect('#rme-main-road-select', nearby.residential_roads.concat(nearby.main_roads));
        populateSelect('#rme-flooded-road-select', nearby.residential_roads.concat(nearby.main_roads));
        populateSelect('#rme-blocked-road-select', nearby.residential_roads.concat(nearby.main_roads));
        populateSelect('#rme-highway-select', nearby.highways);
        populateSelect('#rme-main-st-select', nearby.main_roads.concat(nearby.residential_roads));

        // Populate POI dropdowns
        populatePoiSelect('#rme-hospital-select', nearby.hospitals);
        populatePoiSelect('#rme-gas-station-select', nearby.gas_stations);
        populatePoiSelect('#rme-local-store-select', nearby.farm_stores);
    }

    function populateSelect(selector, items) {
        var $select = $(selector);
        $select.find('option:gt(0)').remove();
        $.each(items, function(i, item) {
            var label = typeof item === 'string' ? item : item.name;
            $select.append($('<option>').val(label).text(label));
        });
    }

    function populatePoiSelect(selector, items) {
        var $select = $(selector);
        $select.find('option:gt(0)').remove();
        $.each(items, function(i, item) {
            var label = item.name;
            if (item.address) label += ' (' + item.address + ')';
            var value = item.name + (item.address ? ', ' + item.address : '');
            $select.append($('<option>').val(value).text(label));
        });
    }

    // Generate Scenario
    $('#rme-generate-btn').on('click', function() {
        var $btn = $(this);
        var $spinner = $('#rme-generate-spinner');

        // Collect form data
        var postData = {
            action: 'rme_eoc_generate',
            nonce: rmeEoc.nonce,
            class_size: $('#rme-class-size').val(),
            formats: []
        };

        if ($('#rme-format-pdf').is(':checked')) postData.formats.push('pdf');
        if ($('#rme-format-txt').is(':checked')) postData.formats.push('txt');

        $.each(configFields, function(fieldId, configKey) {
            postData[configKey] = $('#rme-' + fieldId).val();
        });

        // Validate
        var missing = [];
        $.each(configFields, function(fieldId, configKey) {
            if (!postData[configKey]) missing.push(configKey);
        });
        if (missing.length > 0) {
            showError('#rme-generate-error', 'Please fill in all fields: ' + missing.join(', '));
            return;
        }

        $btn.prop('disabled', true);
        $spinner.addClass('is-active');
        hideError('#rme-generate-error');

        $.post(rmeEoc.ajaxUrl, postData, function(response) {
            $btn.prop('disabled', false);
            $spinner.removeClass('is-active');

            if (!response.success) {
                showError('#rme-generate-error', response.data);
                return;
            }

            showDownloadLinks(response.data);
            $('#rme-eoc-step-2').hide();
            $('#rme-eoc-step-3').show();
            $('html, body').animate({ scrollTop: $('#rme-eoc-step-3').offset().top - 40 }, 300);
        }).fail(function() {
            $btn.prop('disabled', false);
            $spinner.removeClass('is-active');
            showError('#rme-generate-error', 'Generation failed. Check the server logs.');
        });
    });

    // Show download links
    function showDownloadLinks(data) {
        $('#rme-generate-summary p').text(data.summary);

        var $links = $('#rme-download-links').empty();
        var files = data.files;

        if (files.print_all) {
            $links.append('<h3>Print-All (Facilitator + All Students)</h3>');
            $links.append(downloadLink(files.print_all));
        }

        if (files.facilitator) {
            $links.append('<h3>Facilitator Reference Only</h3>');
            $links.append(downloadLink(files.facilitator));
        }

        if (files.txt) {
            $links.append('<h3>Text File (All Tasks)</h3>');
            $links.append(downloadLink(files.txt));
        }

        if (files.students && files.students.length > 0) {
            $links.append('<h3>Individual Student Cards</h3>');
            $.each(files.students, function(i, file) {
                $links.append(downloadLink(file));
            });
        }
    }

    function downloadLink(file) {
        return '<span class="rme-download-item"><a href="' + file.url + '" class="button">' +
            '<span class="dashicons dashicons-download" style="margin-top:4px;"></span> ' +
            file.name + '</a></span>';
    }

    // New scenario
    $('#rme-new-btn').on('click', function() {
        $('#rme-eoc-step-3').hide();
        $('#rme-eoc-step-1').show();
        $('html, body').animate({ scrollTop: 0 }, 300);
    });

    function showError(selector, msg) {
        $(selector).show().find('p').text(msg);
    }

    function hideError(selector) {
        $(selector).hide();
    }

})(jQuery);
