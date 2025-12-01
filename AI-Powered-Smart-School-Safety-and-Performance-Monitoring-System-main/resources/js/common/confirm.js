function confirmAlert(title, message, onSubmit, actionType) {
    let buttons = {
        cancel: function () {
            // Close the dialog
        }
    };

    // Customize the confirm button based on actionType
    if (actionType === 'delete') {
        buttons.confirm = {
            text: 'Delete',
            action: onSubmit
        };

    } else if (actionType === 'edit') {
        buttons.confirm = {
            text: 'Edit',
            action: onSubmit
        };
    } else {
        // Default case (if actionType is unspecified)
        buttons.confirm = onSubmit;
    }

    $.confirm({
        title: title,
        content: message,
        buttons: buttons
    });
}

// Click handler for .delete
$(document).on("click", ".delete", function (e) {
    e.preventDefault();
    
    let url = $(this).data("href");
    let title = $(this).data("title");
    let message = $(this).data("message");
    let type = $(this).data("type") ?? 'url';

    confirmAlert(title, message, function () { 
        switch (type) {
            case 'url':
                window.location = url;
                break;
            default:
                break;
        }
    }, 'delete'); // Pass 'delete' as actionType
});

// Click handler for .edit
$(document).on("click", ".edit", function (e) {
    e.preventDefault();
    
    let url = $(this).data("href");
    let title = $(this).data("title");
    let message = $(this).data("message");
    let type = $(this).data("type") ?? 'url';

    confirmAlert(title, message, function () { 
        switch (type) {
            case 'url':
                window.location = url;
                break;
            default:
                break;
        }
    }, 'edit'); // Pass 'edit' as actionType
});