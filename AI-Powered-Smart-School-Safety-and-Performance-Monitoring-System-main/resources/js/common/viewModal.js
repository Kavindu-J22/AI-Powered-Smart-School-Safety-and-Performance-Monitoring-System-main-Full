$(document).ready(function () {
    $(document).on("click", ".show-modal", function (e) {
        e.preventDefault();
        let url = $(this).attr("href");

        $.confirm({
            title: "Company Details",
            content: '<div class="text-center">Loading...</div>',
            animation: "scaleX",
            closeAnimation: "scaleX",
            animationBounce: 1.5,
            animationSpeed: 300,
            columnClass: "col-md-8",
            buttons: {
                close: {
                    text: "Close",
                    btnClass: "btn-secondary",
                    action: function () {
                        this.close();
                    },
                },
            },
            onOpenBefore: function () {
                let dialog = this;
                $.ajax({
                    type: "GET",
                    url: url,
                    success: function (response) {
                        dialog.setContent(response);
                    },
                    error: function () {
                        dialog.setContent(
                            '<div class="alert alert-danger">Error loading data. Please try again.</div>'
                        );
                    },
                });
            },
        });
    });
});