/**
 *
 * @author Yahya <yahya6789@gmail.com>
 */

$(document).ready(function() {
    $(".preview").on("click", function(e){
        e.preventDefault();

        var media = $(this).attr('href');
        $('#video_player').attr("src", media);
        $('#video_modal').modal('show');
    });

    $("#video_modal").on("hidden.bs.modal", function(e) {
        $('#video_player').get(0).pause();
    });

    $(".delete-file").on("click", function(e) {
        return confirm("Delete file?");
    });

    $(".back-button").on("click", function(e){
        e.preventDefault();
        window.history.back();
    });
});