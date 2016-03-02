$(document).ready(function () {

    /**
     * Use datepicker for date fields.
     */
    $(".datepicker").datepicker({
        "format": "yyyy-mm-dd",
    });

    /**
     * Set focus to the primary input.
     */
    $(".focus-me").focus();

    /**
     * For 'nav-only' submit buttons, disable all required inputs.
     */
    $(":input.nav-only").click(function () {
        $(this).parents("form").find(":input[required]").prop("required", false);
    });
});