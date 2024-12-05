<?php
namespace Imi\SpiderIntegration;

use ExternalModules\AbstractExternalModule;

class SpiderIntegration extends AbstractExternalModule {
    // Method to get certificate content
    private function getP12Certificate() {
        $edocId = $this->getProjectSetting('p12-certificate');
        if (empty($edocId)) {
            return null;
        }
            
        // Get file path from REDCap's edocs table
        $sql = "SELECT stored_name, doc_name FROM redcap_edocs_metadata WHERE doc_id = ?";
        $result = $this->query($sql, [$edocId]);
        $file = $result->fetch_assoc();
        
        if ($file) {
            $filePath = EDOC_PATH . $file['stored_name'];
            if (file_exists($filePath)) {
                return file_get_contents($filePath);
            }
        }
        return null;
    }
    
        // Method to get certificate password
        private function getP12Password() {
            return $this->getProjectSetting('p12-password');
        }



    public function redcap_data_entry_form($project_id, $record, $instrument, $event_id) {
        $fieldName = $this->getProjectSetting('pseudonym-field');
        ?>
        <style>
            .generate-pseudonym-btn:hover {
                background-color: #286090 !important;
                border-color: #2e6da4 !important;
            }
            
            .generate-pseudonym-btn {
                margin-top: 5px !important;
                margin-bottom: 5px !important;
                vertical-align: middle !important;
                background-color: #337AB7 !important;
                border-color: #204d74 !important;
                font-size: 13px !important;
            }
        </style>
        <script>
            $(document).ready(function() {
                const fieldName = <?php echo json_encode($fieldName); ?>;
                const $field = $(`[name='${fieldName}']`);
                
                if ($field.length) {
                    // Make field read-only
                    $field.prop('readonly', true);
                    
                    // Add button next to field
                    const $button = $('<button>', {
                        text: 'Generate Pseudonym',
                        class: 'btn btn-sm btn-primary generate-pseudonym-btn'
                    }).on('click', function(e) {
                        e.preventDefault();
                        openPseudonymDialog($field);
                    });
                    
                    $field.after($button);
                }
            });

            
            function openPseudonymDialog($field) {
                const dialog = $(`
                    <div id="pseudonym-dialog" title="Generate Pseudonym">
                        <form id="pseudonym-form">
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
                                <button type="submit" class="btn btn-sm btn-primary generate-pseudonym-btn">Generate</button>
                            </div>
                        </form>
                    </div>
                `);

                dialog.find('form').on('submit', function(e) {
                    e.preventDefault();
                    const firstName = $('#first_name').val();
                    const lastName = $('#last_name').val();
                    const birthdate = $('#birthdate').val();
                    
                    // Generate a random pseudonym for testing
                    const randomNum = Math.floor(Math.random() * 10000);
                    const pseudonym = `PSN_${firstName.substring(0, 2)}${lastName.substring(0, 2)}_${randomNum}`;
                    
                    // Set the value in the field
                    $field.val(pseudonym);
                    
                    // Close dialog
                    dialog.dialog('close');
                });

                dialog.dialog({
                    modal: true,
                    width: 400,
                    close: function() {
                        $(this).remove();
                    }
                });
            }
        </script>
        <?php
    }
}
?>