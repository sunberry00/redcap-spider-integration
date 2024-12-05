$(document).ready(function() {
    // Initialize field based on module settings
    function initializePseudonymField() {
        const fieldName = pseudonymConfig.fieldName;
        if (!fieldName) return;

        // Find all instances of the field (for repeating forms/events)
        $(`[name='${fieldName}']`).each(function() {
            const $field = $(this);
            
            // Make field read-only
            $field.prop('readonly', true);
            
            // Add custom styling to indicate read-only state
            $field.css({
                'background-color': '#f8f9fa',
                'cursor': 'not-allowed'
            });

            // Create generate button if it doesn't exist
            if ($field.next('.generate-pseudonym-btn').length === 0) {
                const $button = $('<button>', {
                    text: 'Generate Pseudonym',
                    class: 'btn btn-sm btn-primary generate-pseudonym-btn',
                    css: {
                        'margin-left': '5px'
                    }
                });

                // Insert button after the field
                $field.after($button);

                // Add click handler
                $button.on('click', function(e) {
                    e.preventDefault();
                    openPseudonymDialog($field);
                });
            }
        });
    }

    // Create and open dialog
    function openPseudonymDialog($targetField) {
        const dialog = $(`
            <div id="pseudonym-dialog" title="Generate Pseudonym">
                <form id="pseudonym-form" class="p-3">
                    <div class="form-group mb-3">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" class="form-control" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="birthdate">Birth Date:</label>
                        <input type="date" id="birthdate" class="form-control" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Generate</button>
                    </div>
                </form>
            </div>
        `);

        // Handle form submission
        dialog.find('form').on('submit', function(e) {
            e.preventDefault();
            generatePseudonym({
                firstName: $('#first_name').val(),
                lastName: $('#last_name').val(),
                birthdate: $('#birthdate').val()
            }, $targetField);
        });

        // Initialize dialog
        dialog.dialog({
            modal: true,
            width: 400,
            close: function() {
                $(this).dialog('destroy').remove();
            }
        });
    }

    // Generate pseudonym via AJAX
    function generatePseudonym(data, $targetField) {
        $.ajax({
            url: pseudonymConfig.moduleUrl,
            method: 'POST',
            data: data,
            beforeSend: function() {
                // Show loading state
                $('#pseudonym-dialog button[type="submit"]')
                    .prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Generating...');
            },
            success: function(response) {
                if (response.success) {
                    // Update the target field
                    $targetField.val(response.pseudonym);
                    $('#pseudonym-dialog').dialog('close');
                } else {
                    alert('Error generating pseudonym: ' + response.error);
                }
            },
            error: function(xhr, status, error) {
                alert('Error communicating with server: ' + error);
            },
            complete: function() {
                // Reset button state
                $('#pseudonym-dialog button[type="submit"]')
                    .prop('disabled', false)
                    .text('Generate');
            }
        });
    }

    // Initialize when document is ready
    initializePseudonymField();

    // Also initialize when a new instance is added (for repeating forms)
    $(document).on('repeatingFormCreated', function() {
        initializePseudonymField();
    });
});