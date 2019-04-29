$('.flash-message').each(function () {
    const $this = $(this), type = $this.data('flashType'), text = $this.text();

    toastr[type](text);
});
