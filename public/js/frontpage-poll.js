function addOption() {
    var currentCount = $('#poll-options > div.option').length;
    var template = $('#poll-options span.template').data('template');
    template = template.replace(/__index__/g, currentCount);

    $(template).insertBefore('#poll-options div.add-option');

    return false;
}

function removeOption() {
    var currentCount = $("#poll-options > .option").length - 1;
    if (currentCount >= 0){
        $('#option'+currentCount).remove();
    }
}