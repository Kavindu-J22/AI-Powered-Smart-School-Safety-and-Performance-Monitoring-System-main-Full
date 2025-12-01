// Notification Manager JavaScript
class NotificationManager {
  constructor() {
    this.container = document.getElementById("notificationContainer");
    this.notifications = new Map();
    this.defaultDuration = 5000; // 5 seconds
  }

  show(type, title, message, duration = null) {
    const id = this.generateId();
    const notification = this.createNotification(
      id,
      type,
      title,
      message,
      duration || this.defaultDuration
    );

    this.container.appendChild(notification);
    this.notifications.set(id, notification);

    // Start auto-dismiss timer
    this.startAutoDismiss(id, duration || this.defaultDuration);

    return id;
  }

  createNotification(id, type, title, message, duration) {
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.setAttribute("data-id", id);

    const icon = this.getIcon(type);

    notification.innerHTML = `
            <span class="notification-icon material-symbols-outlined">${icon}</span>
            <div class="notification-content">
                <div class="notification-title">${title}</div>
                <div class="notification-message">${message}</div>
            </div>
            <button class="notification-close material-symbols-outlined" onclick="notificationManager.dismiss('${id}')">close</button>
            <div class="notification-progress" style="width: 100%"></div>
        `;

    return notification;
  }

  getIcon(type) {
    const icons = {
      success: "check_circle",
      error: "error",
      danger: "error",
      warning: "warning",
      info: "info",
    };
    return icons[type] || "notifications";
  }

  startAutoDismiss(id, duration) {
    const notification = this.notifications.get(id);
    if (!notification) return;

    const progressBar = notification.querySelector(".notification-progress");

    // Animate progress bar
    progressBar.style.transition = `width ${duration}ms linear`;
    progressBar.style.width = "0%";

    // Auto dismiss
    setTimeout(() => {
      this.dismiss(id);
    }, duration);
  }

  dismiss(id) {
    const notification = this.notifications.get(id);
    if (!notification) return;

    notification.classList.add("removing");

    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
      this.notifications.delete(id);
    }, 300);
  }

  dismissAll() {
    this.notifications.forEach((_, id) => {
      this.dismiss(id);
    });
  }

  generateId() {
    return (
      "notification_" +
      Date.now() +
      "_" +
      Math.random().toString(36).substr(2, 9)
    );
  }

  // Convenience methods
  success(title, message, duration) {
    return this.show("success", title, message, duration);
  }

  error(title, message, duration) {
    return this.show("error", title, message, duration);
  }

  warning(title, message, duration) {
    return this.show("warning", title, message, duration);
  }

  info(title, message, duration) {
    return this.show("info", title, message, duration);
  }
}

// Global instance
window.notificationManager = new NotificationManager();

// Laravel Flash Message Integration
document.addEventListener("DOMContentLoaded", function () {
  // This will be replaced by blade template variables when used
  // The actual session checks will remain in the blade file
  console.log("Notification manager initialized");
});
