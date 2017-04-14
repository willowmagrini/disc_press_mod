(function($) {
    'use strict';

    var syncCollectionStatus;
    var syncImagesStatus;
    var jsonArray = {pages: []};
    var recordCount = {added: 0, updated: 0, removed: 0};

    $(function() {

        $('#syncCollectionBtn').on('click', function() {
            if(syncCollectionStatus == 'syncing') {
                stopCollectionSyncing();
            }
            else {
                $('#syncCollectionResponse').html("Fetching records from Discogs...");
                getJson(1);
            }
        });

        function stopCollectionSyncing() {
            syncCollectionStatus = 'stop';
            $('#syncCollectionBtn').html("Stopping...");
            $('#syncCollectionBtn').prop('disabled', true);
        }

        function getJson(page) {
            syncCollectionStatus = 'syncing';
            $('#syncCollectionBtn').html("Stop syncing");
            $('#syncImagesBtn').prop('disabled', true);
            $.ajax({
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'discpressGetJson',
                    page: page
                },
                url: ajaxurl,
                success: function(result) {
                    if(syncCollectionStatus == 'stop') {
                        syncCollectionStatus = null;
                        $('#syncCollectionResponse').html("");
                        $('#syncCollectionProgress').attr('value', '').attr('max', '').hide();
                        $('#syncCollectionBtn').prop('disabled', false).html("Sync your collection");
                        $('#syncImagesBtn').prop('disabled', false);
                        jsonArray = {pages: []};
                        recordCount = {added: 0, updated: 0, removed: 0};
                    }
                    else if(result.status != 'error') {
                        jsonArray.pages.push(result);
                        $('#syncCollectionResponse').html("Fetching records from Discogs... " + parseInt(result.releases.length + $('#syncCollectionProgress').attr('value')) + " / " + result.pagination.items);
                        $('#syncCollectionProgress').show().attr('max', result.pagination.items).attr('value', parseInt($('#syncCollectionProgress').attr('value') + result.releases.length));
                        if(result.pagination.page < result.pagination.pages) {
                            getJson(parseInt(result.pagination.page + 1));
                        }
                        else {
                            $('#syncCollectionResponse').html("Sync in progress...");
                            $('#syncCollectionProgress').attr('value', '');
                            parseRecords(jsonArray.pages[0]);
                        }
                    }
                    else {
                        syncCollectionStatus = null;
                        $('#syncCollectionResponse').html("An error occurred while contacting Discogs.");
                        $('#syncCollectionProgress').attr('value', '').attr('max', '').hide();
                        $('#syncCollectionBtn').html("Sync images");
                        $('#syncImagesBtn').prop('disabled', false);
                        jsonArray = {pages: []};
                        recordCount = {added: 0, updated: 0, removed: 0};
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    /*console.log(textStatus + " - " + errorThrown);*/
                    syncCollectionStatus = null;
                    $('#syncCollectionResponse').html("An error occurred.");
                    $('#syncCollectionProgress').attr('value', '').attr('max', '').hide();
                    $('#syncCollectionBtn').html("Sync images");
                    $('#syncImagesBtn').prop('disabled', false);
                    jsonArray = {pages: []};
                    recordCount = {added: 0, updated: 0, removed: 0};
                }
            });
        }

        function parseRecords(json) {
            $.ajax({
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'discpressParseRecords',
                    json: JSON.stringify(json)
                },
                url: ajaxurl,
                success: function(result) {
                  console.log(result);
                    if(syncCollectionStatus == 'stop') {
                        syncCollectionStatus = null;
                        $('#syncCollectionResponse').html("");
                        $('#syncCollectionProgress').attr('value', '').attr('max', '').hide();
                        $('#syncCollectionBtn').prop('disabled', false).html("Sync your collection");
                        $('#syncImagesBtn').prop('disabled', false);
                        jsonArray = {pages: []};
                        recordCount = {added: 0, updated: 0, removed: 0};
                    }
                    else {
                        recordCount['added'] += result.records.added;
                        recordCount['updated'] += result.records.updated;
                        if(json.pagination.page < json.pagination.pages) {
                            $('#syncCollectionResponse').html("Sync in progress... " + parseInt(json.releases.length + $('#syncCollectionProgress').attr('value')) + " / " + json.pagination.items);
                            $('#syncCollectionProgress').attr('value', parseInt($('#syncCollectionProgress').attr('value') + json.releases.length));
                            parseRecords(jsonArray.pages[parseInt(json.pagination.page)]);
                        }
                        else {
                            $('#syncCollectionProgress').attr('value', parseInt($('#syncCollectionProgress').attr('value') + json.releases.length));
                            removeOldRecords();
                        }
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    /*console.log(textStatus + " - " + errorThrown);*/
                    syncCollectionStatus = null;
                    $('#syncCollectionResponse').html("An error occurred.");
                    $('#syncCollectionProgress').attr('value', '').attr('max', '').hide();
                    $('#syncCollectionBtn').html("Sync images");
                    $('#syncImagesBtn').prop('disabled', false);
                    jsonArray = {pages: []};
                    recordCount = {added: 0, updated: 0, removed: 0};
                }
            });
        }

        function removeOldRecords() {
            $('#syncCollectionResponse').html("Removing old records...");
            $.ajax({
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'discpressRemoveOldRecords',
                    json: JSON.stringify(jsonArray)
                },
                url: ajaxurl,
                success: function(result) {
                    if(syncCollectionStatus == 'stop') {
                        syncCollectionStatus = null;
                        $('#syncCollectionResponse').html("");
                        $('#syncCollectionProgress').attr('value', '').attr('max', '').hide();
                        $('#syncCollectionBtn').prop('disabled', false).html("Sync your collection");
                        $('#syncImagesBtn').prop('disabled', false);
                        jsonArray = {pages: []};
                        recordCount = {added: 0, updated: 0, removed: 0};
                    }
                    else {
                        syncCollectionStatus = null;
                        recordCount['removed'] += result.records.removed;
                        $('#syncCollectionResponse').html("<strong>Sync complete!</strong> Added " + recordCount.added + " records, updated " + recordCount.updated + " records, removed " + recordCount.removed + " records");
                        $('#syncCollectionProgress').attr('value', '').attr('max', '').hide();
                        $('#syncCollectionBtn').html("Sync your collection");
                        $('#syncImagesBlock').show();
                        $('#syncImagesBtn').prop('disabled', false);
                        jsonArray = {pages: []};
                        recordCount = {added: 0, updated: 0, removed: 0};
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    /*console.log(textStatus + " - " + errorThrown);*/
                    syncCollectionStatus = null;
                    $('#syncCollectionResponse').html("An error occurred.");
                    $('#syncCollectionProgress').attr('value', '').attr('max', '').hide();
                    $('#syncCollectionBtn').html("Sync images");
                    $('#syncImagesBtn').prop('disabled', false);
                    jsonArray = {pages: []};
                    recordCount = {added: 0, updated: 0, removed: 0};
                }
            });
        }

        $("#syncImagesBtn").on("click", function() {
            if(syncImagesStatus == 'syncing') {
                stopImagesSyncing();
            }
            else {
                checkImagesStatus();
            }
        });

        function stopImagesSyncing() {
            syncImagesStatus = 'stop';
            $('#syncImagesBtn').html("Stopping...");
            $('#syncImagesBtn').prop('disabled', true);
        }

        function checkImagesStatus() {
            $('#syncImagesResponse').html("Checking...");
            $('#syncImagesBtn').html("Stop syncing");
            $('#syncCollectionBtn').prop('disabled', true);
            $.ajax({
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'discpressCheckImagesStatus'
                },
                url: ajaxurl,
                success: function(result) {
                    if(syncImagesStatus == 'stop') {
                        syncImagesStatus = null;
                        $('#syncImagesResponse').html("");
                        $('#syncImagesProgress').attr('value', '').attr('max', '').hide();
                        $('#syncImagesBtn').prop('disabled', false).html("Sync images");
                        $('#syncCollectionBtn').prop('disabled', false);
                    }
                    else if(result.status == 'start') {
                        syncImagesStatus = 'syncing';
                        $('#syncImagesResponse').html("Syncing... " + 0 + " / " + result.tot);
                        $('#syncImagesProgress').show().attr('max', result.tot);
                        downloadCall(0, result.tot);
                    }
                    else if(result.status == 'nothing') {
                        syncImagesStatus = null;
                        $('#syncImagesResponse').html("Nothing to sync.");
                        $('#syncImagesProgress').attr('value', '').attr('max', '').hide();
                        $('#syncImagesBtn').html("Sync images");
                        $('#syncCollectionBtn').prop('disabled', false);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    /*console.log(textStatus + " - " + errorThrown);*/
                    syncImagesStatus = null;
                    $('#syncImagesResponse').html("An error occurred.");
                    $('#syncImagesProgress').attr('value', '').attr('max', '').hide();
                    $('#syncImagesBtn').html("Sync images");
                    $('#syncCollectionBtn').prop('disabled', false);
                }
            });
        }

        function downloadCall(n, tot) {
            $.ajax({
                type: 'post',
                dataType: 'json',
                data: {
                    action: 'discpressImportImages'
                },
                url: ajaxurl,
                success: function(result) {
                    if(syncImagesStatus == 'stop') {
                        syncImagesStatus = null;
                        $('#syncImagesResponse').html("");
                        $('#syncImagesProgress').attr('value', '').attr('max', '').hide();
                        $('#syncImagesBtn').prop('disabled', false).html("Sync images");
                        $('#syncCollectionBtn').prop('disabled', false);
                    }
                    else if(result.status == 'syncing') {
                        n = parseInt(n + 1);
                        $('#syncImagesResponse').html("Syncing... " + parseInt($('#syncImagesProgress').attr('value') + 1) + " / " + tot);
                        $('#syncImagesProgress').attr('value', parseInt($('#syncImagesProgress').attr('value') + 1));
                        downloadCall(n, tot);
                    }
                    else if(result.status == 'done') {
                        syncImagesStatus = null;
                        $('#syncImagesResponse').html("<strong>Done!</strong>");
                        $('#syncImagesProgress').attr('value', '').attr('max', '').hide();
                        $('#syncImagesBtn').html("Sync images");
                        $('#syncCollectionBtn').prop('disabled', false);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    /*console.log(textStatus + " - " + errorThrown);*/
                    syncImagesStatus = null;
                    $('#syncImagesResponse').html("An error occurred.");
                    $('#syncImagesProgress').attr('value', '').attr('max', '').hide();
                    $('#syncImagesBtn').html("Sync images");
                    $('#syncCollectionBtn').prop('disabled', false);
                }
            });
        }

    });

})(jQuery);
