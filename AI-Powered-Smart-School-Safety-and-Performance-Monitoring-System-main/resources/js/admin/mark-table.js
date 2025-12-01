// Mark Table enhancements
document.addEventListener("DOMContentLoaded", function () {
    setTimeout(function () {
        try {
            const table = $("#mark-table").DataTable();
            $(".dataTables_filter input").attr(
                "placeholder",
                "Search by student, subject, year, term..."
            );

            // Simple responsive adjustments
            function handleResponsive() {
                const isMobile = window.innerWidth <= 768;
                if (isMobile) {
                    table.page.len(10).draw();
                    $(".dataTables_length").hide();
                } else {
                    $(".dataTables_length").show();
                }
            }

            handleResponsive();

            let resizeTimer;
            window.addEventListener("resize", function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () {
                    handleResponsive();
                    table.responsive.recalc();
                }, 250);
            });
        } catch (e) {
            // DataTable may not be initialized yet
            // console.log('Mark table init error', e);
        }
    }, 100);
});
