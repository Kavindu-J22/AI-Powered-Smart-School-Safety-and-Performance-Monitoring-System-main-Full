// Refined Dashboard JavaScript with Subtle Animations
document.addEventListener("DOMContentLoaded", function () {
    // Simple intersection observer for gentle animations
    const observerOptions = {
        threshold: 0.2,
        rootMargin: "0px 0px -30px 0px",
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = "1";
                entry.target.style.transform = "translateY(0)";
            }
        });
    }, observerOptions);

    // Initialize gentle fade-in for dashboard elements
    const elements = document.querySelectorAll(
        ".stat-card, .chart-container, .recent-activity-item"
    );
    elements.forEach((element) => {
        element.style.opacity = "0";
        element.style.transform = "translateY(15px)";
        element.style.transition = "opacity 0.5s ease, transform 0.5s ease";
        observer.observe(element);
    });

    // Subtle hover effects for stat cards
    const statCards = document.querySelectorAll(".stat-card");
    statCards.forEach((card) => {
        card.addEventListener("mouseenter", function () {
            this.style.transform = "translateY(-3px)";
            this.style.boxShadow = "0 8px 25px rgba(6, 193, 103, 0.15)";
        });

        card.addEventListener("mouseleave", function () {
            this.style.transform = "translateY(0)";
            this.style.boxShadow = "";
        });
    });

    // Gentle number counting animation
    function animateNumber(element, target, duration = 1200) {
        const startTime = Date.now();
        const startValue = 0;

        function update() {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Smooth easing function
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(
                startValue + (target - startValue) * easeOut
            );

            element.textContent = current.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                element.textContent = target.toLocaleString();
            }
        }

        requestAnimationFrame(update);
    }

    // Apply number animation to stat numbers
    const statNumbers = document.querySelectorAll(".stat-number");
    const numberObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (
                    entry.isIntersecting &&
                    !entry.target.hasAttribute("data-animated")
                ) {
                    const targetValue = parseInt(
                        entry.target.textContent.replace(/,/g, "")
                    );
                    if (!isNaN(targetValue)) {
                        entry.target.textContent = "0";
                        entry.target.setAttribute("data-animated", "true");

                        setTimeout(() => {
                            animateNumber(entry.target, targetValue);
                        }, 200);
                    }
                }
            });
        },
        { threshold: 0.6 }
    );

    statNumbers.forEach((number) => numberObserver.observe(number));

    // (Removed duplicate scroll-to-top button logic. Only the main back-to-top button remains.)

    // Add minimal CSS for smooth transitions
    const style = document.createElement("style");
    style.textContent = `
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .recent-activity-item:hover {
            background-color: #f8fffe;
            border-color: #c7f2dd;
        }

        .quick-action-btn:hover {
            transform: translateY(-1px);
        }

        /* Respect user's motion preferences */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    `;
    document.head.appendChild(style);

    console.log("Subtle dashboard animations initialized! ✨");
});

// ======= School Settings & Theme Customization Functions =======

// Toggle settings panel visibility
function toggleSettingsPanel() {
    const panel = document.getElementById("settings-panel");
    const icon = document.getElementById("settings-toggle-icon");

    if (panel.style.display === "none" || panel.style.display === "") {
        panel.style.display = "block";
        icon.textContent = "expand_less";
        // Animate panel appearance
        panel.style.opacity = "0";
        panel.style.transform = "translateY(-20px)";
        setTimeout(() => {
            panel.style.transition = "all 0.3s ease";
            panel.style.opacity = "1";
            panel.style.transform = "translateY(0)";
        }, 10);
    } else {
        panel.style.transition = "all 0.3s ease";
        panel.style.opacity = "0";
        panel.style.transform = "translateY(-20px)";
        setTimeout(() => {
            panel.style.display = "none";
            icon.textContent = "expand_more";
        }, 300);
    }
}

// Theme customization functions
function updateThemePreview() {
    const primaryColor = document.getElementById("primary_color").value;
    const secondaryColor = document.getElementById("secondary_color").value;
    const accentColor = document.getElementById("accent_color").value;

    // Update text inputs
    document.getElementById("primary_color_text").value = primaryColor;
    document.getElementById("secondary_color_text").value = secondaryColor;
    document.getElementById("accent_color_text").value = accentColor;

    // Apply colors immediately for preview
    applyThemeColors(primaryColor, secondaryColor, accentColor);

    // Show preview badge
    showColorPreview(primaryColor, secondaryColor, accentColor);
}

function updateColorFromText(colorType) {
    const textInput = document.getElementById(colorType + "_text");
    const colorInput = document.getElementById(colorType);

    if (isValidHexColor(textInput.value)) {
        colorInput.value = textInput.value;
        updateThemePreview();
    } else {
        // Show error for invalid color
        textInput.style.borderColor = "#EF4444";
        setTimeout(() => {
            textInput.style.borderColor = "";
        }, 2000);
    }
}

function isValidHexColor(hex) {
    return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex);
}

function showColorPreview(primary, secondary, accent) {
    // Create or update preview badge
    let previewBadge = document.getElementById("color-preview-badge");
    if (!previewBadge) {
        previewBadge = document.createElement("div");
        previewBadge.id = "color-preview-badge";
        previewBadge.style.cssText = `
      position: fixed;
      top: 80px;
      right: 20px;
      background: white;
      padding: 10px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 1050;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      font-weight: 500;
      color: #374151;
    `;
        document.body.appendChild(previewBadge);
    }

    previewBadge.innerHTML = `
    <span>Preview:</span>
    <div style="width: 20px; height: 20px; background: ${primary}; border-radius: 4px; border: 1px solid #e5e7eb;"></div>
    <div style="width: 20px; height: 20px; background: ${secondary}; border-radius: 4px; border: 1px solid #e5e7eb;"></div>
    <div style="width: 20px; height: 20px; background: ${accent}; border-radius: 4px; border: 1px solid #e5e7eb;"></div>
    <button onclick="hideColorPreview()" style="background: none; border: none; color: #6B7280; cursor: pointer; padding: 2px;">×</button>
  `;

    previewBadge.style.display = "flex";
}

function hideColorPreview() {
    const previewBadge = document.getElementById("color-preview-badge");
    if (previewBadge) {
        previewBadge.style.display = "none";
    }
}

function applyThemeColors(primary, secondary, accent) {
    const root = document.documentElement;

    // Apply to our CSS variables defined in head.blade.php
    root.style.setProperty("--primary-green", primary);
    root.style.setProperty("--light-green", secondary);
    root.style.setProperty("--dark-green", secondary);
    root.style.setProperty("--accent-green", accent);
    root.style.setProperty("--success-green", secondary);

    // Also update Bootstrap CSS variables
    root.style.setProperty("--bs-primary", primary);
    root.style.setProperty("--bs-secondary", secondary);
    root.style.setProperty("--bs-success", secondary);

    // Calculate RGB values for Bootstrap
    const primaryRgb = hexToRgb(primary);
    const secondaryRgb = hexToRgb(secondary);

    root.style.setProperty("--bs-primary-rgb", primaryRgb);
    root.style.setProperty("--bs-secondary-rgb", secondaryRgb);
    root.style.setProperty("--bs-success-rgb", secondaryRgb);

    // Update specific elements that use these colors
    const statIcons = document.querySelectorAll(".stat-icon");
    statIcons.forEach((icon) => {
        icon.style.background = `linear-gradient(135deg, ${primary}, ${secondary})`;
    });

    const quickActionBtns = document.querySelectorAll(".quick-action-btn");
    quickActionBtns.forEach((btn) => {
        btn.style.background = `linear-gradient(135deg, ${primary}, ${secondary})`;
    });

    // Update any existing primary colored elements
    const primaryElements = document.querySelectorAll(
        ".bg-gradient-primary, .btn-primary"
    );
    primaryElements.forEach((element) => {
        element.style.background = `linear-gradient(135deg, ${primary}, ${secondary})`;
    });
}

// Helper function to convert hex to RGB
function hexToRgb(hex) {
    // Remove # if present
    hex = hex.replace("#", "");

    // Convert hex to RGB
    if (hex.length === 3) {
        hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
    }

    if (hex.length !== 6) {
        return "6, 193, 103"; // Default green RGB
    }

    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);

    return `${r}, ${g}, ${b}`;
}

function applyThemePreset(theme) {
    const presets = {
        green: { primary: "#06C167", secondary: "#10B981", accent: "#F0FDF4" },
        blue: { primary: "#3B82F6", secondary: "#60A5FA", accent: "#EFF6FF" },
        orange: { primary: "#F59E0B", secondary: "#FBBF24", accent: "#FFFBEB" },
        red: { primary: "#EF4444", secondary: "#F87171", accent: "#FEF2F2" },
        purple: { primary: "#8B5CF6", secondary: "#A78BFA", accent: "#F5F3FF" },
    };

    if (presets[theme]) {
        document.getElementById("primary_color").value = presets[theme].primary;
        document.getElementById("secondary_color").value =
            presets[theme].secondary;
        document.getElementById("accent_color").value = presets[theme].accent;
        updateThemePreview();
    }
}

function resetTheme() {
    document.getElementById("primary_color").value = "#06C167";
    document.getElementById("secondary_color").value = "#10B981";
    document.getElementById("accent_color").value = "#F0FDF4";
    document.getElementById("theme_mode").value = "light";
    document.getElementById("enable_animations").checked = true;
    updateThemePreview();
}

// AJAX functions for saving settings
function updateSchoolInfo() {
    const formData = new FormData();
    formData.append(
        "_token",
        document.querySelector('input[name="_token"]').value
    );
    formData.append(
        "school_name",
        document.getElementById("school_name").value
    );
    formData.append(
        "school_type",
        document.getElementById("school_type").value
    );
    formData.append(
        "school_motto",
        document.getElementById("school_motto").value
    );
    formData.append(
        "principal_name",
        document.getElementById("principal_name").value
    );
    formData.append(
        "established_year",
        document.getElementById("established_year").value
    );
    formData.append(
        "total_capacity",
        document.getElementById("total_capacity").value
    );
    formData.append(
        "website_url",
        document.getElementById("website_url").value
    );

    showLoadingState("school-info-form");

    fetch("/admin/settings/update-school-info", {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest",
        },
    })
        .then((response) => response.json())
        .then((data) => {
            hideLoadingState("school-info-form");
            if (data.success) {
                showNotification(
                    "School information updated successfully!",
                    "success"
                );
            } else {
                showNotification(
                    data.message || "Error updating school information",
                    "error"
                );
            }
        })
        .catch((error) => {
            hideLoadingState("school-info-form");
            console.error("Error:", error);
            showNotification("Error updating school information", "error");
        });
}

function updateThemeColors() {
    const formData = new FormData();
    formData.append(
        "_token",
        document.querySelector('input[name="_token"]').value
    );
    formData.append(
        "primary_color",
        document.getElementById("primary_color").value
    );
    formData.append(
        "secondary_color",
        document.getElementById("secondary_color").value
    );
    formData.append(
        "accent_color",
        document.getElementById("accent_color").value
    );
    formData.append("theme_mode", document.getElementById("theme_mode").value);
    formData.append(
        "enable_animations",
        document.getElementById("enable_animations").checked ? 1 : 0
    );

    showLoadingState("theme-form");

    fetch("/admin/settings/update-theme", {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest",
        },
    })
        .then((response) => response.json())
        .then((data) => {
            hideLoadingState("theme-form");
            if (data.success) {
                showNotification("Theme updated successfully!", "success");
                // Refresh page to apply changes fully
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(
                    data.message || "Error updating theme",
                    "error"
                );
            }
        })
        .catch((error) => {
            hideLoadingState("theme-form");
            console.error("Error:", error);
            showNotification("Error updating theme", "error");
        });
}

function updateAcademicSettings() {
    const formData = new FormData();
    formData.append(
        "_token",
        document.querySelector('input[name="_token"]').value
    );
    formData.append(
        "school_start_time",
        document.getElementById("school_start_time").value
    );
    formData.append(
        "school_end_time",
        document.getElementById("school_end_time").value
    );
    formData.append(
        "academic_year_start",
        document.getElementById("academic_year_start").value
    );
    formData.append(
        "academic_year_end",
        document.getElementById("academic_year_end").value
    );

    showLoadingState("academic-form");

    fetch("/admin/settings/update-academic", {
        method: "POST",
        body: formData,
        headers: {
            "X-Requested-With": "XMLHttpRequest",
        },
    })
        .then((response) => response.json())
        .then((data) => {
            hideLoadingState("academic-form");
            if (data.success) {
                showNotification(
                    "Academic settings updated successfully!",
                    "success"
                );
            } else {
                showNotification(
                    data.message || "Error updating academic settings",
                    "error"
                );
            }
        })
        .catch((error) => {
            hideLoadingState("academic-form");
            console.error("Error:", error);
            showNotification("Error updating academic settings", "error");
        });
}

// Utility functions
function showLoadingState(formId) {
    const form = document.getElementById(formId);
    const buttons = form.querySelectorAll("button");
    buttons.forEach((btn) => {
        btn.disabled = true;
        btn.innerHTML =
            '<i class="material-symbols-rounded spinning me-1">sync</i>Saving...';
    });
}

function hideLoadingState(formId) {
    const form = document.getElementById(formId);
    const buttons = form.querySelectorAll("button");
    buttons.forEach((btn) => {
        btn.disabled = false;
        // Restore original button text based on button type
        if (btn.textContent.includes("Saving...")) {
            if (formId === "school-info-form") {
                btn.innerHTML =
                    '<i class="material-symbols-rounded me-1">save</i>Update School Info';
            } else if (formId === "theme-form") {
                btn.innerHTML =
                    '<i class="material-symbols-rounded me-1">save</i>Save Theme';
            } else if (formId === "academic-form") {
                btn.innerHTML =
                    '<i class="material-symbols-rounded me-1">save</i>Update Academic Settings';
            }
        }
    });
}

function showNotification(message, type = "info") {
    // Create notification element
    const notification = document.createElement("div");
    notification.className = `alert alert-${
        type === "error" ? "danger" : type
    } alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  `;
    notification.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Add spinning animation for loading buttons
const spinningStyle = document.createElement("style");
spinningStyle.textContent = `
  .spinning {
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
`;
document.head.appendChild(spinningStyle);
