/*
 * This script will handle all javascript functions neeeded for the admin
 * pages.
 * Depends: jquery, photo.js
 */

Photo.loadPage = function (resource) {
    $.getJSON(resource, function (data) {
        console.log(data);
        $("#album").html('<div class="row"></div>');
        $.each(data.albums, function (i, album) {
            href = 'photo/album/' + album.id
            $("#album").append('<div class="col-lg-3 col-md-4 col-xs-6 thumb">'
                    + '<a class="thumbnail" href="' + href + '">'
                    + '<img class="img-responsive" src="/data/photo/' + album.coverPath + '" alt="">'
                    + '<input type="checkbox" class="thumbail-checkbox">'
                    + album.name
                    + '</a>'
                    + '</div>');
        });

        $("#album").find("a").each(function () {
            $(this).on('click', function (e) {
                e.preventDefault();
                Photo.loadPage(e.target.href);

            });
        });

        $.each(data.photos, function (i, photo) {
            href = 'photo/photo/' + photo.id
            $("#album").append('<div class="col-lg-3 col-md-4 col-xs-6 thumb">'
                    + '<a class="thumbnail" href="' + href + '">'
                    + '<img class="img-responsive" src="/data/photo/' + photo.smallThumbPath + '" alt="">'
                    + '<input type="checkbox" class="thumbail-checkbox">'
                    + '</a>'
                    + '</div>');
        });
        $("#paging").html('');

        $.each(data.pages, function (i, page) {
            href = 'photo/album/' + data.album.id + '/' + page;
            if (page === data.activepage)
            {
                $("#paging").append('<li class="active"><a href="' + href + '">' + (page + 1) + '</a></li>');
            } else {
                $("#paging").append('<li><a href="' + href + '">' + (page + 1) + '</a></li>');
            }
        });
        if (data.activepage > 0)
        {
            href = 'photo/album/' + data.album.id + '/' + (data.activepage - 1);
            $("#paging").prepend('<li><a id="previous" href="' + href + '">'
                    + '<span aria-hidden="true">«</span>'
                    + '<span class="sr-only">Previous</span>'
                    + '</a></li>');
        }
        if (data.activepage < data.lastpage) {
            href = 'photo/album/' + data.album.id + '/' + (data.activepage + 1);
            $("#paging").append('<li><a id="next" href="' + href + '">'
                    + '<span aria-hidden="true">»</span>'
                    + '<span class="sr-only">Previous</span>'
                    + '</a></li>');
        }

        $("#paging").find("a").each(function () {
            $(this).on('click', function (e) {
                e.preventDefault();
                Photo.loadPage(e.target.href);

            });
        });
    });
}

Photo.initAdmin = function () {
    var COUNT_SPAN = '<span id="remove-count"></span>'
    $("#remove-multiple").html($("#remove-multiple").html().replace('%i', COUNT_SPAN));
    var count = 0;
    $(".thumbail-checkbox").change(function () {
        if (this.checked) {
            count++;
        } else {
            count--;
        }
        $("#remove-count").html(count);
        if (count > 0)
        {
            console.log("unhide");
            $("#remove-multiple").removeClass("btn-hidden");
        } else {
            $("#remove-multiple").addClass("btn-hidden");
        }
    });
}
$.fn.extend({
    treed: function () {
        //initialize each of the top levels
        var tree = $(this);
        tree.addClass("tree");
        tree.find('li').has("ul").each(function () {
            var branch = $(this); //li with children ul
            branch.prepend("<i class='indicator glyphicon glyphicon-plus-sign'></i>");
            branch.addClass('branch');
            branch.on('click', function (e) {
                if (this == e.target) {
                    var icon = $(this).children('i:first');
                    icon.toggleClass("glyphicon-minus-sign glyphicon-plus-sign");
                    $(this).children().children().toggle();
                }
            })
            branch.children().children().toggle();
        });
        //fire event from the dynamically added icon
        $('.branch .indicator').on('click', function () {
            $(this).closest('li').click();
        });
        //fire event to open branch if the li contains an anchor instead of text
        $('.branch>a').each(function () {
            $(this).on('click', function (e) {
                $(this).closest('li').click();
                e.preventDefault();
                // Photo.loadPage(e.target.href);

            });
        });
        tree.find("a").each(function () {
            $(this).on('click', function (e) {
                e.preventDefault();
                Photo.loadPage(e.target.href);

            });
        });
    }
});


