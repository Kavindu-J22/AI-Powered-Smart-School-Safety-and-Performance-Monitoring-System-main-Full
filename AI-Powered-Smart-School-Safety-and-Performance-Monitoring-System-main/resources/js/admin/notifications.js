class NotificationManager {
  constructor() {
    this.apiBaseUrl = "/admin/notifications";
    this.pollInterval = 30000; // 30 seconds
    this.isPolling = false;
    this.notificationContainer = document.getElementById(
      "notifications-container"
    );
    this.notificationBadge = document.getElementById("notification-badge");
    this.notificationCount = document.getElementById("notification-count");
    this.markAllReadBtn = document.getElementById("mark-all-read");

    this.init();
  }

  init() {
    // Load initial notifications
    this.loadNotifications();

    // Start polling for new notifications
    this.startPolling();

    // Bind event listeners
    this.bindEvents();
  }

  bindEvents() {
    // Mark all as read button
    if (this.markAllReadBtn) {
      this.markAllReadBtn.addEventListener("click", () => {
        this.markAllAsRead();
      });
    }

    // Dropdown show event - load fresh notifications
    const dropdownElement = document.getElementById("dropdownMenuButton");
    if (dropdownElement) {
      dropdownElement.addEventListener("show.bs.dropdown", () => {
        this.loadNotifications();
      });
    }
  }

  async loadNotifications() {
    try {
      const response = await fetch(`${this.apiBaseUrl}?limit=10`, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content"),
        },
      });

      if (!response.ok) {
        throw new Error("Failed to load notifications");
      }

      const data = await response.json();
      this.renderNotifications(data.notifications);
      this.updateBadge(data.unread_count);
    } catch (error) {
      console.error("Error loading notifications:", error);
      this.showError("Failed to load notifications");
    }
  }

  renderNotifications(notifications) {
    if (!this.notificationContainer) return;

    if (notifications.length === 0) {
      this.notificationContainer.innerHTML = `
                <li class="text-center text-muted py-3">
                    <i class="material-symbols-rounded">notifications_none</i>
                    <p class="mb-0">No notifications yet</p>
                </li>
            `;
      return;
    }

    const notificationsHtml = notifications
      .map((notification) => {
        const iconColorClass = this.getIconColorClass(notification.color);
        const readClass = notification.is_read ? "opacity-75" : "";

        return `
                <li class="mb-2">
                    <a class="dropdown-item border-radius-md ${readClass}" 
                       href="javascript:;" 
                       data-notification-id="${notification.id}"
                       onclick="notificationManager.markAsRead(${notification.id})">
                        <div class="d-flex py-1">
                            <div class="my-auto">
                                <div class="avatar avatar-sm ${iconColorClass} me-3">
                                    <i class="material-symbols-rounded text-white">${notification.icon}</i>
                                </div>
                            </div>
                            <div class="d-flex flex-column justify-content-center">
                                <h6 class="text-sm font-weight-normal mb-1">
                                    <span class="font-weight-bold">${notification.title}</span>
                                </h6>
                                <p class="text-xs mb-1">${notification.message}</p>
                                <p class="text-xs text-secondary mb-0">
                                    <i class="material-symbols-rounded" style="font-size: 12px;">schedule</i>
                                    ${notification.time_ago}
                                </p>
                            </div>
                        </div>
                    </a>
                </li>
            `;
      })
      .join("");

    this.notificationContainer.innerHTML = notificationsHtml;
  }

  getIconColorClass(color) {
    const colorMap = {
      success: "bg-gradient-success",
      warning: "bg-gradient-warning",
      danger: "bg-gradient-danger",
      info: "bg-gradient-info",
    };
    return colorMap[color] || "bg-gradient-secondary";
  }

  updateBadge(unreadCount) {
    if (!this.notificationBadge || !this.notificationCount) return;

    if (unreadCount > 0) {
      this.notificationCount.textContent =
        unreadCount > 99 ? "99+" : unreadCount;
      this.notificationBadge.style.display = "block";
    } else {
      this.notificationBadge.style.display = "none";
    }
  }

  async markAsRead(notificationId) {
    try {
      const response = await fetch(`${this.apiBaseUrl}/mark-as-read`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content"),
        },
        body: JSON.stringify({
          notification_ids: [notificationId],
        }),
      });

      if (!response.ok) {
        throw new Error("Failed to mark notification as read");
      }

      // Update UI
      const notificationElement = document.querySelector(
        `[data-notification-id="${notificationId}"]`
      );
      if (notificationElement) {
        notificationElement.classList.add("opacity-75");
      }

      // Refresh unread count
      this.updateUnreadCount();
    } catch (error) {
      console.error("Error marking notification as read:", error);
    }
  }

  async markAllAsRead() {
    try {
      const response = await fetch(`${this.apiBaseUrl}/mark-all-as-read`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content"),
        },
      });

      if (!response.ok) {
        throw new Error("Failed to mark all notifications as read");
      }

      // Reload notifications
      this.loadNotifications();
    } catch (error) {
      console.error("Error marking all notifications as read:", error);
    }
  }

  async updateUnreadCount() {
    try {
      const response = await fetch(`${this.apiBaseUrl}/unread-count`, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content"),
        },
      });

      if (!response.ok) {
        throw new Error("Failed to get unread count");
      }

      const data = await response.json();
      this.updateBadge(data.unread_count);
    } catch (error) {
      console.error("Error getting unread count:", error);
    }
  }

  startPolling() {
    if (this.isPolling) return;

    this.isPolling = true;
    this.pollTimer = setInterval(() => {
      this.updateUnreadCount();
    }, this.pollInterval);
  }

  stopPolling() {
    if (this.pollTimer) {
      clearInterval(this.pollTimer);
      this.pollTimer = null;
    }
    this.isPolling = false;
  }

  showError(message) {
    if (!this.notificationContainer) return;

    this.notificationContainer.innerHTML = `
            <li class="text-center text-danger py-3">
                <i class="material-symbols-rounded">error</i>
                <p class="mb-0">${message}</p>
            </li>
        `;
  }
}

// Initialize notification manager when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  window.notificationManager = new NotificationManager();
});

// Cleanup on page unload
window.addEventListener("beforeunload", function () {
  if (window.notificationManager) {
    window.notificationManager.stopPolling();
  }
});
