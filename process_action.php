<?php
require_once '../layouts/functions.php';
require_login();
require_permission($pdo, 'view_dashboard');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $redirect = $_POST['redirect'] ?? 'dashboard.php';

    try {
        switch ($action) {
            case 'add_user':
                require_permission($pdo, 'manage_users');
                $name = clean($_POST['full_name']);
                $email = clean($_POST['email']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $role_id = $_POST['role_id'];

                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $email, $password, $role_id]);

                // If role is Driver, handle driver entry
                $role_stmt = $pdo->prepare("SELECT id FROM roles WHERE LOWER(name) = 'driver'");
                $role_stmt->execute();
                $driver_role_id = $role_stmt->fetchColumn();
                if ($role_id == $driver_role_id) {
                    $user_id = $pdo->lastInsertId();
                    $stmt = $pdo->prepare("INSERT INTO drivers (user_id, license_number) VALUES (?, ?)");
                    $stmt->execute([$user_id, 'PENDING-'.time()]);
                }
                break;

            case 'add_driver':
                require_permission($pdo, 'manage_users');
                $name = clean($_POST['full_name']);
                $email = clean($_POST['email']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $role_id = $_POST['role_id'];
                $phone = clean($_POST['phone']);
                $license_no = clean($_POST['license_no']);
                $bus_number = clean($_POST['bus_number']);

                // Insert into users
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, phone, role_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $password, $phone, $role_id]);
                $user_id = $pdo->lastInsertId();

                // Insert into drivers
                $stmt = $pdo->prepare("INSERT INTO drivers (user_id, license_number, bus_number) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $license_no, $bus_number]);
                break;

            case 'edit_user':
                require_permission($pdo, 'manage_users');
                $id = $_POST['id'];
                $name = clean($_POST['full_name']);
                $email = clean($_POST['email']);
                
                if (isset($_POST['role_id'])) {
                    $role_id = $_POST['role_id'];
                } else {
                    $role_stmt = $pdo->prepare("SELECT role_id FROM users WHERE id = ?");
                    $role_stmt->execute([(int)$id]);
                    $role_id = $role_stmt->fetchColumn();
                }
                $status = clean($_POST['status']);
                $phone = clean($_POST['phone'] ?? '');

                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, password = ?, phone = ?, role_id = ?, status = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $password, $phone, $role_id, $status, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, role_id = ?, status = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $phone, $role_id, $status, $id]);
                }

                // If role was changed to Driver and they don't have a driver record, create one
                $role_stmt = $pdo->prepare("SELECT id FROM roles WHERE LOWER(name) = 'driver'");
                $role_stmt->execute();
                $driver_role_id = $role_stmt->fetchColumn();
                if ($role_id == $driver_role_id) {
                    $chk = $pdo->prepare("SELECT id FROM drivers WHERE user_id = ?");
                    $chk->execute([$id]);
                    if (!$chk->fetch()) {
                        $stmt = $pdo->prepare("INSERT INTO drivers (user_id, license_number) VALUES (?, ?)");
                        $stmt->execute([$id, 'PENDING-'.time()]);
                    }
                }
                break;

            case 'edit_driver':
                require_permission($pdo, 'manage_users');
                $user_id = $_POST['user_id'];
                $license = clean($_POST['license_number']);
                $phone = clean($_POST['phone_number']);
                $bus = clean($_POST['bus_number']);
                
                $stmt = $pdo->prepare("UPDATE drivers SET license_number = ?, bus_number = ? WHERE user_id = ?");
                $stmt->execute([$license, $bus, $user_id]);
                
                $stmt2 = $pdo->prepare("UPDATE users SET phone = ? WHERE id = ?");
                $stmt2->execute([$phone, $user_id]);
                break;

            case 'delete_user':
                require_permission($pdo, 'manage_users');
                $id = $_POST['id'];
                if ($id != $_SESSION['user_id']) {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$id]);
                }
                break;

            case 'add_role':
                require_permission($pdo, 'manage_roles');
                $name = clean($_POST['name']);
                $description = clean($_POST['description']);
                
                $stmt = $pdo->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
                $stmt->execute([$name, $description]);
                break;

            case 'edit_role':
                require_permission($pdo, 'manage_roles');
                $id = $_POST['id'];
                $name = clean($_POST['name']);
                $description = clean($_POST['description']);
                
                $stmt = $pdo->prepare("UPDATE roles SET name = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $description, $id]);
                break;

            case 'delete_role':
                require_permission($pdo, 'manage_roles');
                $id = $_POST['id'];
                
                // Prevent deleting Super Admin
                if ($id != 1) {
                    $stmt = $pdo->prepare("DELETE FROM roles WHERE id = ?");
                    $stmt->execute([$id]);
                }
                break;

            case 'add_permission':
                require_permission($pdo, 'manage_roles');
                $name = clean($_POST['name']);
                $description = clean($_POST['description']);
                
                $stmt = $pdo->prepare("INSERT INTO permissions (name, description) VALUES (?, ?)");
                $stmt->execute([$name, $description]);
                break;

            case 'edit_permission':
                require_permission($pdo, 'manage_roles');
                $id = $_POST['id'];
                $name = clean($_POST['name']);
                $description = clean($_POST['description']);
                
                $stmt = $pdo->prepare("UPDATE permissions SET name = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $description, $id]);
                break;

            case 'delete_permission':
                require_permission($pdo, 'manage_roles');
                $id = $_POST['id'];
                
                $stmt = $pdo->prepare("DELETE FROM permissions WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'add_route':
                require_permission($pdo, 'manage_routes');
                $name = clean($_POST['route_name']);
                $start = clean($_POST['start_point']);
                $end = clean($_POST['end_point']);
                $time = $_POST['departure_time'];
                $distance = !empty($_POST['distance_km']) ? (float)$_POST['distance_km'] : 0.00;
                $driver = !empty($_POST['driver_id']) ? $_POST['driver_id'] : null;

                $stmt = $pdo->prepare("INSERT INTO routes (route_name, start_point, end_point, departure_time, distance_km, driver_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $start, $end, $time, $distance, $driver]);
                break;

            case 'edit_route':
                require_permission($pdo, 'manage_routes');
                $id = $_POST['id'];
                $name = clean($_POST['route_name']);
                $start = clean($_POST['start_point']);
                $end = clean($_POST['end_point']);
                $time = $_POST['departure_time'];
                $distance = !empty($_POST['distance_km']) ? (float)$_POST['distance_km'] : 0.00;
                $driver = !empty($_POST['driver_id']) ? $_POST['driver_id'] : null;

                $stmt = $pdo->prepare("UPDATE routes SET route_name = ?, start_point = ?, end_point = ?, departure_time = ?, distance_km = ?, driver_id = ? WHERE id = ?");
                $stmt->execute([$name, $start, $end, $time, $distance, $driver, $id]);
                break;

            case 'delete_route':
                require_permission($pdo, 'manage_routes');
                $id = $_POST['id'];
                
                $stmt = $pdo->prepare("DELETE FROM routes WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'add_bus_point':
                require_permission($pdo, 'manage_routes');
                $route_id = $_POST['route_id'];
                $name = clean($_POST['point_name']);
                $time = $_POST['arrival_time'];
                $order = $_POST['sequence_order'];
                $lat = $_POST['latitude'] ?? null;
                $lng = $_POST['longitude'] ?? null;

                $stmt = $pdo->prepare("INSERT INTO bus_points (route_id, point_name, arrival_time, sequence_order, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$route_id, $name, $time, $order, $lat, $lng]);
                break;

            case 'edit_bus_point':
                require_permission($pdo, 'manage_routes');
                $id = $_POST['id'];
                $route_id = $_POST['route_id'];
                $name = clean($_POST['point_name']);
                $time = $_POST['arrival_time'];
                $order = $_POST['sequence_order'];
                $lat = $_POST['latitude'] ?? null;
                $lng = $_POST['longitude'] ?? null;

                $stmt = $pdo->prepare("UPDATE bus_points SET route_id = ?, point_name = ?, arrival_time = ?, sequence_order = ?, latitude = ?, longitude = ? WHERE id = ?");
                $stmt->execute([$route_id, $name, $time, $order, $lat, $lng, $id]);
                break;

            case 'delete_bus_point':
                require_permission($pdo, 'manage_routes');
                $id = $_POST['id'];
                
                $stmt = $pdo->prepare("DELETE FROM bus_points WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'add_bus':
                require_permission($pdo, 'manage_routes');
                $bus_no = clean($_POST['bus_number']);
                $plate = clean($_POST['plate_number']);
                $capacity = (int)$_POST['capacity'];
                $status = $_POST['status'];

                $stmt = $pdo->prepare("INSERT INTO buses (bus_number, plate_number, capacity, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$bus_no, $plate, $capacity, $status]);
                break;

            case 'edit_bus':
                require_permission($pdo, 'manage_routes');
                $id = $_POST['id'];
                $bus_no = clean($_POST['bus_number']);
                $plate = clean($_POST['plate_number']);
                $capacity = (int)$_POST['capacity'];
                $status = $_POST['status'];

                $stmt = $pdo->prepare("UPDATE buses SET bus_number = ?, plate_number = ?, capacity = ?, status = ? WHERE id = ?");
                $stmt->execute([$bus_no, $plate, $capacity, $status, $id]);
                break;

            case 'delete_bus':
                require_permission($pdo, 'manage_routes');
                $id = $_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM buses WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'add_trip':
                require_permission($pdo, 'manage_routes');
                $route_id = $_POST['route_id'];
                $bus_id = $_POST['bus_id'];
                $driver_id = $_POST['driver_id'];
                $date = $_POST['trip_date'];
                $time = $_POST['start_time'];

                $stmt = $pdo->prepare("INSERT INTO trips (route_id, bus_id, driver_id, trip_date, start_time, status) VALUES (?, ?, ?, ?, ?, 'scheduled')");
                $stmt->execute([$route_id, $bus_id, $driver_id, $date, $time]);
                break;

            case 'edit_trip':
                require_permission($pdo, 'manage_routes');
                $id = $_POST['id'];
                $route_id = $_POST['route_id'];
                $bus_id = $_POST['bus_id'];
                $driver_id = $_POST['driver_id'];
                $date = $_POST['trip_date'];
                $time = $_POST['start_time'];
                $status = $_POST['status'];

                $stmt = $pdo->prepare("UPDATE trips SET route_id = ?, bus_id = ?, driver_id = ?, trip_date = ?, start_time = ?, status = ? WHERE id = ?");
                $stmt->execute([$route_id, $bus_id, $driver_id, $date, $time, $status, $id]);
                break;

            case 'delete_trip':
                require_permission($pdo, 'manage_routes');
                $id = $_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ?");
                $stmt->execute([$id]);
                break;

            case 'add_notification':
                require_permission($pdo, 'manage_users');
                $user_type = $_POST['user_type'];
                $title = clean($_POST['title']);
                $message = clean($_POST['message']);

                // In a real system, might insert multiple rows per user or general broadcast
                // For simplicity, we insert one record
                $stmt = $pdo->prepare("INSERT INTO notifications (user_id, user_type, title, message) VALUES (NULL, ?, ?, ?)");
                $stmt->execute([$user_type, $title, $message]);
                break;

            case 'delete_notification':
                require_permission($pdo, 'manage_users');
                $id = $_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
                $stmt->execute([$id]);
                break;
        }

        header("Location: $redirect?msg=success");
    } catch (Exception $e) {
        header("Location: $redirect?msg=error&err=" . urlencode($e->getMessage()));
    }
    exit();
}
?>
