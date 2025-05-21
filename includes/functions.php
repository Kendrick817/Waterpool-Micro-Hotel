<?php
// Helper Functions
// This file has useful functions that are used in multiple pages

/**
 * This function displays room features as nice-looking badges
 * It takes a string or array of features and turns each one into a badge
 *
 * @param mixed $features String or array of features
 * @return string HTML output of feature badges
 */
function display_features($features) {
    $output = '';

    // If features is a string (old format), convert it to array
    if (is_string($features)) {
        $features_array = preg_split('/[,;\n]+/', $features);
    } else {
        $features_array = $features;
    }

    foreach ($features_array as $feature) {
        $feature = trim($feature);
        if (!empty($feature)) {
            $output .= '<span class="badge rounded-pill bg-light text-dark text-wrap me-1 mb-1">' . htmlspecialchars($feature) . '</span>';
        }
    }

    return $output;
}

/**
 * Get room features
 *
 * @param mysqli $conn Database connection
 * @param int $room_id Room ID
 * @return array Array of feature names
 */
function get_room_features($conn, $room_id) {
    // Validate input
    $room_id = intval($room_id);
    if ($room_id <= 0) {
        return [];
    }

    try {
        // Get features directly from the rooms table
        $query = "SELECT features FROM rooms WHERE room_ID = ?";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("i", $room_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                // Split the features string into an array
                $features_str = $row['features'];
                if (empty($features_str)) {
                    return [];
                }

                // Split by commas, semicolons, or newlines and trim each feature
                $features = array_map('trim', preg_split('/[,;\n]+/', $features_str));

                // Remove empty elements
                return array_filter($features, function($feature) {
                    return !empty($feature);
                });
            }
        }
    } catch (Exception $e) {
        error_log("Error in get_room_features: " . $e->getMessage());
    }

    return [];
}

/**
 * Get room facilities
 *
 * @param mysqli $conn Database connection
 * @param int $room_id Room ID
 * @return array Array of facility objects with id, name, description, and icon
 */
function get_room_facilities($conn, $room_id) {
    // Validate input
    $room_id = intval($room_id);
    if ($room_id <= 0) {
        return [];
    }

    try {
        // First, get the facilities string from the room
        $query = "SELECT facilities FROM rooms WHERE room_ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$row = $result->fetch_assoc()) {
            return [];
        }

        // If facilities column is empty, return empty array
        if (empty($row['facilities'])) {
            return [];
        }

        // Split the facilities string into an array
        $facility_names = array_map('trim', preg_split('/[,;\n]+/', $row['facilities']));

        // Remove empty elements
        $facility_names = array_filter($facility_names, function($name) {
            return !empty($name);
        });

        if (empty($facility_names)) {
            return [];
        }

        // Check if facilities table exists
        $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'facilities'");
        $has_facilities_table = mysqli_num_rows($check_table) > 0;

        $facilities = [];

        if ($has_facilities_table) {
            // Try to match facility names with the facilities table
            foreach ($facility_names as $name) {
                // Create a placeholder for the facility
                $facility = [
                    'facility_ID' => 0,
                    'name' => $name,
                    'description' => '',
                    'icon' => 'default_feature.png'
                ];

                // Try to find a matching facility in the database
                $facility_query = "SELECT * FROM facilities WHERE name LIKE ? LIMIT 1";
                $facility_stmt = $conn->prepare($facility_query);

                if ($facility_stmt) {
                    $search_name = "%$name%";
                    $facility_stmt->bind_param("s", $search_name);
                    $facility_stmt->execute();
                    $facility_result = $facility_stmt->get_result();

                    if ($facility_row = $facility_result->fetch_assoc()) {
                        // Use the database facility if found
                        $facility = $facility_row;
                    }
                }

                $facilities[] = $facility;
            }
        } else {
            // If no facilities table, just create simple facility objects
            foreach ($facility_names as $name) {
                $facilities[] = [
                    'facility_ID' => 0,
                    'name' => $name,
                    'description' => '',
                    'icon' => 'default_feature.png'
                ];
            }
        }

        return $facilities;
    } catch (Exception $e) {
        error_log("Error in get_room_facilities: " . $e->getMessage());
        return [];
    }
}

/**
 * This function filters rooms based on search criteria
 * It helps find available rooms based on dates, guests, and facilities
 *
 * @param mysqli $conn Database connection
 * @param array $filters Array of filter criteria
 * @return array Array of room objects
 */
function filter_rooms($conn, $filters = []) {
    // For simplicity, we'll just use the original function
    // This avoids checking for normalized tables which might not exist
    return filter_rooms_original($conn, $filters);
}

/**
 * Optimized filter_rooms function
 *
 * @param mysqli $conn Database connection
 * @param array $filters Array of filter criteria
 * @return array Array of room objects
 */
function filter_rooms_original($conn, $filters = []) {
    // Start with a base query
    $query = "SELECT * FROM rooms WHERE 1=1";
    $params = [];
    $types = "";

    // Check if check-in and check-out dates are provided
    if (!empty($filters['checkin']) && !empty($filters['checkout'])) {
        // Validate dates
        $checkin = date('Y-m-d', strtotime($filters['checkin']));
        $checkout = date('Y-m-d', strtotime($filters['checkout']));

        if ($checkin && $checkout) {
            // Add a subquery to exclude rooms that are already booked for the selected dates
            $query .= " AND room_ID NOT IN (
                SELECT room_ID FROM bookings
                WHERE (check_in <= ? AND check_out >= ?)
                OR (check_in <= ? AND check_out >= ?)
                OR (check_in >= ? AND check_out <= ?)
            )";
            $types .= "ssssss";
            $params[] = $checkout;
            $params[] = $checkin;
            $params[] = $checkin;
            $params[] = $checkout;
            $params[] = $checkin;
            $params[] = $checkout;
        }
    }

    // Filter by facilities
    if (!empty($filters['facilities']) && is_array($filters['facilities'])) {
        foreach ($filters['facilities'] as $facility) {
            // Sanitize the facility name
            $facility = trim($facility);
            if (!empty($facility)) {
                // Search in both features and facilities columns
                $query .= " AND (features LIKE ? OR facilities LIKE ?)";
                $types .= "ss";
                $params[] = "%$facility%";
                $params[] = "%$facility%";
            }
        }
    }

    // Filter by guests (adults + children)
    if (!empty($filters['adults']) || !empty($filters['children'])) {
        $adults = !empty($filters['adults']) ? intval($filters['adults']) : 0;
        $children = !empty($filters['children']) ? intval($filters['children']) : 0;
        $total_guests = $adults + $children;

        if ($total_guests > 0) {
            $query .= " AND max_guests >= ?";
            $types .= "i";
            $params[] = $total_guests;
        }
    }

    // Add sorting - allow custom sorting if provided
    if (!empty($filters['sort'])) {
        switch ($filters['sort']) {
            case 'price_high':
                $query .= " ORDER BY price DESC";
                break;
            case 'name':
                $query .= " ORDER BY name ASC";
                break;
            case 'guests':
                $query .= " ORDER BY max_guests DESC";
                break;
            default:
                $query .= " ORDER BY price ASC"; // Default: price low to high
        }
    } else {
        $query .= " ORDER BY price ASC"; // Default sort by price low to high
    }

    // Add pagination if requested
    if (!empty($filters['limit']) && is_numeric($filters['limit'])) {
        $limit = intval($filters['limit']);
        $offset = !empty($filters['offset']) && is_numeric($filters['offset']) ? intval($filters['offset']) : 0;
        $query .= " LIMIT ?, ?";
        $types .= "ii";
        $params[] = $offset;
        $params[] = $limit;
    }

    try {
        // Prepare and execute the query
        $stmt = $conn->prepare($query);

        // If prepare was successful, continue with binding parameters
        if ($stmt !== false && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        // Execute the query
        if ($stmt !== false) {
            $stmt->execute();
            $result = $stmt->get_result();

            // Fetch all rooms
            $rooms = [];
            if ($result && $result->num_rows > 0) {
                while ($room = $result->fetch_assoc()) {
                    $rooms[] = $room;
                }
            }

            return $rooms;
        }
    } catch (Exception $e) {
        // Log the error
        error_log("Error in filter_rooms: " . $e->getMessage());
    }

    // If anything fails, fall back to a simpler query
    $fallback_query = "SELECT * FROM rooms ORDER BY price ASC";
    $result = $conn->query($fallback_query);

    $rooms = [];
    if ($result && $result->num_rows > 0) {
        while ($room = $result->fetch_assoc()) {
            $rooms[] = $room;
        }
    }

    return $rooms;
}

/**
 * Get a single room with all its details
 *
 * @param mysqli $conn Database connection
 * @param int $room_id Room ID
 * @return array|null Room details or null if not found
 */
function get_room_details($conn, $room_id) {
    // Validate input
    $room_id = intval($room_id);
    if ($room_id <= 0) {
        return null;
    }

    try {
        // Get the room details
        $query = "SELECT * FROM rooms WHERE room_ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$room = $result->fetch_assoc()) {
            return null;
        }

        // Add features and facilities
        $room['features_array'] = get_room_features($conn, $room_id);
        $room['facilities_array'] = get_room_facilities($conn, $room_id);

        return $room;
    } catch (Exception $e) {
        error_log("Error in get_room_details: " . $e->getMessage());
        return null;
    }
}
?>
