// Enhanced Student Table JavaScript
document.addEventListener("DOMContentLoaded", function () {
  // Enhanced DataTable functionality
  setTimeout(function () {
    const table = $("#student-table").DataTable();

    // Add responsive breakpoint handling
    function handleResponsive() {
      const isMobile = window.innerWidth <= 768;
      const isTablet = window.innerWidth <= 992 && window.innerWidth > 768;

      if (isMobile) {
        // Mobile-specific adjustments
        table.page.len(10).draw();
        $(".dataTables_length").hide();
        $(".dataTables_info").addClass("small text-center mt-2");
      } else if (isTablet) {
        // Tablet-specific adjustments
        table.page.len(15).draw();
      } else {
        // Desktop view
        $(".dataTables_length").show();
        $(".dataTables_info").removeClass("small text-center mt-2");
      }
    }

    // Initial responsive setup
    handleResponsive();

    // Handle window resize
    let resizeTimer;
    window.addEventListener("resize", function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        handleResponsive();
        table.responsive.recalc();
      }, 250);
    });

    // Enhanced search functionality
    $(".dataTables_filter input").attr(
      "placeholder",
      "Search students by name, code, email..."
    );

    // Add loading state improvements
    table.on("processing.dt", function (e, settings, processing) {
      if (processing) {
        $(".dataTables_processing").html(`
                    <div class="d-flex flex-column align-items-center">
                        <div class="spinner-border text-primary mb-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <small class="text-muted">Loading students...</small>
                    </div>
                `);
      }
    });

    // Add row hover effects
    $("#student-table tbody")
      .on("mouseenter", "tr", function () {
        $(this).addClass("table-hover-effect");
      })
      .on("mouseleave", "tr", function () {
        $(this).removeClass("table-hover-effect");
      });

    // Enhance empty state
    if (table.data().count() === 0) {
      $(".dataTables_empty").html(`
                <div class="text-center py-5">
                    <i class="material-symbols-outlined display-1 text-muted mb-3">school</i>
                    <h5 class="text-muted">No Students Found</h5>
                    <p class="text-muted">Start by creating your first student record.</p>
                    <a href="${window.location.origin}/admin/management/students/form" class="btn btn-primary">
                        <i class="material-symbols-outlined me-1">add</i>
                        Add Student
                    </a>
                </div>
            `);
    }
  }, 100);
});

// Additional responsive utilities
function adjustTableForMobile() {
  const table = $("#student-table");

  if (window.innerWidth <= 576) {
    table.addClass("table-sm");
    // Hide less important columns on very small screens
    table.find("th:nth-child(6), td:nth-child(6)").hide(); // Email column
    table.find("th:nth-child(8), td:nth-child(8)").hide(); // Status column
  } else {
    table.removeClass("table-sm");
    table.find("th, td").show();
  }
}

// Run on load and resize
window.addEventListener("load", adjustTableForMobile);
window.addEventListener("resize", debounce(adjustTableForMobile, 250));

// Debounce utility function
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}
