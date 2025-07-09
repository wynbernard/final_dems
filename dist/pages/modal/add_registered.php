<!-- Modal for Registering IDPs -->
<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAdminModalLabel">Register IDP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="registerIDPForm">
                    <div class="mb-3">
                        <label for="idpDropdown" class="form-label">Select IDP</label>
                        <select class="form-select" id="idpDropdown" name="idpDropdown" required>
                            <option value="">Select an IDP</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="locationDropdown" class="form-label">Location</label>
                        <select class="form-select" id="locationDropdown" name="locationDropdown" required>
                            <option value="">Select a Location</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="roomDropdown" class="form-label">Room</label>
                        <select class="form-select" id="roomDropdown" name="roomDropdown" required disabled>
                            <option value="">Select a Room</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="disasterDropdown" class="form-label">Disaster</label>
                        <select class="form-select" id="disasterDropdown" name="disasterDropdown" required>
                            <option value="">Select a Disaster</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="registerIDPForm" class="btn btn-primary">Register</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Fetch pre-registered IDPs and populate the dropdown
        $.ajax({
            url: '../fetch_data/fetch_idps.php',
            method: 'GET',
            success: function(data) {
                console.log('Raw Response:', data); // Log the raw response

                try {
                    // Check if the response is already an object (not a string)
                    let idps;
                    if (typeof data === 'string') {
                        idps = JSON.parse(data); // Parse the JSON string
                    } else {
                        idps = data; // Assume the response is already an object
                    }

                    console.log('Parsed IDPs:', idps); // Log the parsed data
                    const idpDropdown = $('#idpDropdown');

                    // Clear existing options
                    idpDropdown.empty().append('<option value="">Select an IDP</option>');

                    // Populate the dropdown
                    idps.forEach(idp => {
                        idpDropdown.append(`<option value="${idp.pre_reg_id}">${idp.f_name} ${idp.l_name}</option>`);
                    });
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });

        // Fetch locations and populate the dropdown
        $.ajax({
            url: '../fetch_data/fetch_location.php',
            method: 'GET',
            success: function(data) {
                console.log('Raw Response:', data); // Log the raw response

                try {
                    // Check if the response is already an object (not a string)
                    let locations;
                    if (typeof data === 'string') {
                        locations = JSON.parse(data); // Parse the JSON string
                    } else {
                        locations = data; // Assume the response is already an object
                    }

                    console.log('Parsed Locations:', locations); // Log the parsed data
                    const locationDropdown = $('#locationDropdown');

                    // Clear existing options
                    locationDropdown.empty().append('<option value="">Select a Location</option>');

                    // Populate the dropdown
                    locations.forEach(location => {
                        locationDropdown.append(`<option value="${location.evac_loc_id}">${location.name}</option>`);
                    });
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Status:', status);
                console.error('XHR Response:', xhr.responseText);
            }
        });

        // Dynamically populate the Room dropdown based on the selected Location
        $('#locationDropdown').on('change', function() {
            const evac_loc_id = $(this).val();
            const roomDropdown = $('#roomDropdown');

            // Clear and disable the Room dropdown if no location is selected
            if (!evac_loc_id) {
                roomDropdown.empty().append('<option value="">Select a Room</option>').prop('disabled', true);
                return;
            }

            // Fetch rooms for the selected location
            $.ajax({
                url: '../fetch_data/fetch_room.php', // Ensure the correct path
                method: 'GET',
                data: {
                    evac_loc_id
                },
                success: function(data) {
                    console.log('Raw Response:', data); // Log the raw response

                    try {
                        // Check if the response is already an object (not a string)
                        let rooms;
                        if (typeof data === 'string') {
                            rooms = JSON.parse(data); // Parse the JSON string
                        } else {
                            rooms = data; // Assume the response is already an object
                        }

                        console.log('Parsed Rooms:', rooms); // Log the parsed data
                        roomDropdown.empty().append('<option value="">Select a Room</option>');

                        if (rooms.length > 0) {
                            rooms.forEach(room => {
                                roomDropdown.append(`<option value="${room.room_id}">${room.room_name}</option>`);
                            });
                            roomDropdown.prop('disabled', false); // Enable the Room dropdown
                        } else {
                            roomDropdown.prop('disabled', true); // Disable if no rooms are available
                        }
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        roomDropdown.empty().append('<option value="">Select a Room</option>').prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    roomDropdown.empty().append('<option value="">Select a Room</option>').prop('disabled', true);
                }
            });
        });

        // Fetch disasters and populate the dropdown
        $.ajax({
            url: '../fetch_data/fetch_disaster.php',
            method: 'GET',
            success: function(data) {
                console.log('Raw Response:', data); // Log the raw response

                try {
                    // Check if the response is already an object (not a string)
                    let disasters;
                    if (typeof data === 'string') {
                        disasters = JSON.parse(data); // Parse the JSON string
                    } else {
                        disasters = data; // Assume the response is already an object
                    }

                    console.log('Parsed Disasters:', disasters); // Log the parsed data
                    const disasterDropdown = $('#disasterDropdown');

                    // Clear existing options
                    disasterDropdown.empty().append('<option value="">Select a Disaster</option>');

                    // Populate the dropdown
                    disasters.forEach(disaster => {
                        disasterDropdown.append(`<option value="${disaster.disaster_id}">${disaster.disaster_name}</option>`);
                    });
                } catch (e) {
                    console.error('Error parsing JSON:', e);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
        // Handle form submission
        $('#registerIDPForm').on('submit', function(event) {
            event.preventDefault();

            // Collect form data
            const pre_reg_id = $('#idpDropdown').val();
            const evac_loc_id = $('#locationDropdown').val();
            const room_id = $('#roomDropdown').val();
            const disaster_id = $('#disasterDropdown').val();

            // Validate inputs
            if (!pre_reg_id || !evac_loc_id || !room_id || !disaster_id) {
                alert('Please fill out all fields.');
                return;
            }

            // Send data to the server
            $.ajax({
                url: '../action/registered_idps.php',
                method: 'POST',
                data: {
                    pre_reg_id,
                    evac_loc_id,
                    room_id,
                    disaster_id
                },
                success: function(response) {
                    alert('IDP registered successfully!');
                    location.reload(); // Refresh the page to reflect changes
                },
                error: function(xhr, status, error) {
                    console.error('Error registering IDP:', error);
                    alert('Failed to register IDP. Please try again.');
                }
            });
        });
    });
</script>

