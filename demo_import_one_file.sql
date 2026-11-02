-- 00: TAO CSDL + NAP DU LIEU + CAI MOCK PAYMENT (MySQL 8.0.16+)
-- Chi chay tren DB moi hotel_mini_card_demo; import 1 LAN.
-- Neu da co database cu hotel_mini thi khong chay file nay. Hay xem README.



-- ============== 01_tao_csdl.sql ==============
-- KHACH SAN MINI - MySQL 8.0+ / InnoDB / utf8mb4
-- Thiết kế tham khảo cho Laravel. Dùng migrations khi lập trình thực tế.
-- Mỗi đơn có thể đặt nhiều phòng, tất cả chung thời gian lưu trú.
-- CHAY TREN DATABASE TRONG. Khong xoa/ghi de cac bang da co.
-- Neu ban DA NHAP schema cu trong hotel_mini, hay dung 05_nang_cap_tu_schema_cu.sql.
CREATE DATABASE IF NOT EXISTS hotel_mini_card_demo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_mini_card_demo;

CREATE TABLE roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL UNIQUE,   -- CLIENT, STAFF, ADMIN
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(80) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(191) NOT NULL UNIQUE,
  phone VARCHAR(20) NULL,
  password VARCHAR(255) NOT NULL, -- chi luu mat khau da hash
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  email_verified_at TIMESTAMP NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
  INDEX idx_user_role_active (role_id, is_active)
) ENGINE=InnoDB;

CREATE TABLE room_types (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  capacity TINYINT UNSIGNED NOT NULL,
  base_price DECIMAL(13,2) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_room_type_capacity CHECK (capacity > 0),
  CONSTRAINT chk_room_type_price CHECK (base_price >= 0)
) ENGINE=InnoDB;

CREATE TABLE rooms (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  room_type_id BIGINT UNSIGNED NOT NULL,
  room_number VARCHAR(20) NOT NULL UNIQUE,
  floor SMALLINT UNSIGNED NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  housekeeping_status ENUM('clean','dirty') NOT NULL DEFAULT 'clean',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_room_type FOREIGN KEY (room_type_id) REFERENCES room_types(id) ON DELETE RESTRICT,
  INDEX idx_room_type_active (room_type_id, is_active)
) ENGINE=InnoDB;

CREATE TABLE room_images (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  room_id BIGINT UNSIGNED NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  is_cover BOOLEAN NOT NULL DEFAULT FALSE,
  display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_image_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  INDEX idx_room_images_order (room_id, display_order)
) ENGINE=InnoDB;

CREATE TABLE bookings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_code VARCHAR(30) NOT NULL UNIQUE,
  user_id BIGINT UNSIGNED NOT NULL,
  contact_name VARCHAR(120) NOT NULL,
  contact_phone VARCHAR(20) NOT NULL,
  guest_count SMALLINT UNSIGNED NOT NULL,
  check_in_date DATE NOT NULL,
  check_out_date DATE NOT NULL,
  status ENUM(
    'pending_approval','pending_payment','confirmed',
    'checked_in','completed','cancelled','rejected','expired'
  ) NOT NULL DEFAULT 'pending_approval',
  total_amount DECIMAL(13,2) NOT NULL DEFAULT 0,
  reviewed_by_user_id BIGINT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  expires_at DATETIME NULL, -- han duyet / han thanh toan cho don dang cho
  cancelled_at DATETIME NULL,
  actual_check_in_at DATETIME NULL,
  actual_check_out_at DATETIME NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
  CONSTRAINT fk_booking_reviewed FOREIGN KEY (reviewed_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT chk_booking_dates CHECK (check_out_date > check_in_date),
  CONSTRAINT chk_booking_guests CHECK (guest_count > 0),
  CONSTRAINT chk_booking_total CHECK (total_amount >= 0),
  INDEX idx_booking_user (user_id, created_at),
  INDEX idx_booking_status_dates (status, check_in_date, check_out_date)
) ENGINE=InnoDB;

CREATE TABLE booking_details (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT UNSIGNED NOT NULL,
  room_id BIGINT UNSIGNED NOT NULL,
  price_per_night DECIMAL(13,2) NOT NULL,
  line_total DECIMAL(13,2) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_detail_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE RESTRICT,
  CONSTRAINT fk_detail_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
  CONSTRAINT chk_detail_price CHECK (price_per_night >= 0 AND line_total >= 0),
  UNIQUE KEY uq_detail_room_booking (booking_id, room_id),
  INDEX idx_detail_room (room_id)
) ENGINE=InnoDB;

CREATE TABLE payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id BIGINT UNSIGNED NOT NULL,
  payment_code VARCHAR(40) NOT NULL UNIQUE,
  provider VARCHAR(50) NULL,
  provider_transaction_id VARCHAR(100) NULL UNIQUE,
  method ENUM('online') NOT NULL DEFAULT 'online',
  amount DECIMAL(13,2) NOT NULL,
  status ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  paid_at DATETIME NULL,
  -- MySQL UNIQUE cho phep nhieu NULL: chi 1 giao dich thanh cong/booking.
  successful_booking_id BIGINT UNSIGNED GENERATED ALWAYS AS
    (CASE WHEN status = 'success' THEN booking_id ELSE NULL END) STORED,
  UNIQUE KEY uq_one_successful_payment (successful_booking_id),
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_payment_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE RESTRICT,
  CONSTRAINT chk_payment_amount CHECK (amount > 0),
  CONSTRAINT chk_paid_at_when_success CHECK (status <> 'success' OR paid_at IS NOT NULL),
  INDEX idx_payment_booking_status (booking_id, status)
) ENGINE=InnoDB;


-- BANG MOI: thong tin KHONG NHAY CAM cua the gia lap cho tung lan thu thanh toan.
-- TUYET DOI KHONG LUU full card number (PAN), CVV/CVC, PIN.
CREATE TABLE card_payment_details (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  payment_id BIGINT UNSIGNED NOT NULL UNIQUE,
  card_brand ENUM('VISA','MASTERCARD','JCB') NOT NULL,
  card_last4 CHAR(4) CHARACTER SET ascii NOT NULL,
  cardholder_name VARCHAR(120) NOT NULL,
  simulation_result ENUM('approved','declined','insufficient_funds') NOT NULL,
  authorization_code VARCHAR(32) NULL,  -- ma chap thuan GIA, chi co neu thanh cong
  processed_at DATETIME NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_card_detail_payment FOREIGN KEY (payment_id)
    REFERENCES payments(id) ON DELETE RESTRICT,
  CONSTRAINT chk_card_last4 CHECK (card_last4 REGEXP '^[0-9]{4}$'),
  CONSTRAINT chk_fake_auth_code CHECK (
    (simulation_result = 'approved' AND authorization_code IS NOT NULL)
    OR (simulation_result <> 'approved' AND authorization_code IS NULL)
  )
) ENGINE=InnoDB;



-- ============== 02_du_lieu_mau.sql ==============
-- 02_DU_LIEU_MAU.SQL | Chay SAU 01_tao_csdl.sql
USE hotel_mini_card_demo;
SET NAMES utf8mb4;
-- Tai khoan CHI de demo; mat khau demo chung: Demo@12345
-- Gia tri password ben duoi la bcrypt HASH tao bang PHP password_hash() (khong phai plain text).

INSERT INTO roles (id, code, name) VALUES
(1,'CLIENT','Khách hàng'),
(2,'STAFF','Nhân viên lễ tân'),
(3,'ADMIN','Quản trị viên');

INSERT INTO permissions (id,code,name) VALUES
(1,'rooms.view','Xem phòng'),
(2,'rooms.manage','Quản lý phòng'),
(3,'room_types.manage','Quản lý loại phòng / giá'),
(4,'bookings.create','Tạo đơn đặt phòng'),
(5,'bookings.view_own','Xem đơn của bản thân'),
(6,'bookings.cancel_own','Hủy đơn của bản thân'),
(7,'payments.create','Thanh toán của bản thân'),
(8,'bookings.view_all','Xem danh sách đơn'),
(9,'bookings.review','Duyệt / từ chối đơn'),
(10,'bookings.checkin','Nhận phòng'),
(11,'bookings.checkout','Trả phòng'),
(12,'users.manage','Quản lý tài khoản'),
(13,'roles.assign','Phân vai trò'),
(14,'revenue.view','Xem doanh thu');

INSERT INTO role_permissions(role_id,permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.code='ADMIN';
INSERT INTO role_permissions(role_id,permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.code='STAFF' AND p.code IN
('rooms.view','bookings.view_all','bookings.review','bookings.checkin','bookings.checkout');
INSERT INTO role_permissions(role_id,permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
WHERE r.code='CLIENT' AND p.code IN
('rooms.view','bookings.create','bookings.view_own','bookings.cancel_own','payments.create');

-- Hash se duoc chen tu script build, dung cho local/dev; thay ngay neu deploy.
INSERT INTO users (id,role_id,name,email,phone,password,is_active) VALUES
(1,3,'Quản trị viên','admin@hotel-demo.local','0901000000','$2y$12$JbaoaDa7v7XQL9aABpxb4ujQysejDTATLEKBvNHoTHwVzvoO8ZG/2',1),
(2,2,'Lễ tân A','staff@hotel-demo.local','0901000010','$2y$12$JbaoaDa7v7XQL9aABpxb4ujQysejDTATLEKBvNHoTHwVzvoO8ZG/2',1),
(3,1,'Nguyễn Minh Anh','minhanh@hotel-demo.local','0901000001','$2y$12$JbaoaDa7v7XQL9aABpxb4ujQysejDTATLEKBvNHoTHwVzvoO8ZG/2',1),
(4,1,'Trần Hải Nam','hainam@hotel-demo.local','0901000002','$2y$12$JbaoaDa7v7XQL9aABpxb4ujQysejDTATLEKBvNHoTHwVzvoO8ZG/2',1),
(5,1,'Lê Thu Hà','thuha@hotel-demo.local','0901000003','$2y$12$JbaoaDa7v7XQL9aABpxb4ujQysejDTATLEKBvNHoTHwVzvoO8ZG/2',1);

INSERT INTO room_types (id,name,capacity,base_price,description) VALUES
(1,'Standard',2,550000,'Phòng tiêu chuẩn, 1 giường đôi'),
(2,'Deluxe',3,850000,'Phòng rộng, tiện nghi nâng cao'),
(3,'Family',4,1200000,'Phòng dành cho gia đình');

INSERT INTO rooms (id,room_type_id,room_number,floor,is_active,housekeeping_status) VALUES
(1,1,'101',1,1,'clean'), (2,1,'102',1,1,'clean'), (3,1,'103',1,1,'clean'),
(4,1,'104',1,1,'clean'), (5,1,'105',1,1,'clean'), (6,1,'106',1,1,'dirty'),
(7,2,'201',2,1,'clean'), (8,2,'202',2,1,'clean'), (9,2,'203',2,1,'clean'),
(10,2,'204',2,0,'dirty'),
(11,3,'301',3,1,'clean'), (12,3,'302',3,1,'clean');

INSERT INTO room_images (room_id,image_path,is_cover,display_order) VALUES
(1,'demo/rooms/101-cover.jpg',1,1), (1,'demo/rooms/101-detail.jpg',0,2),
(2,'demo/rooms/102-cover.jpg',1,1), (3,'demo/rooms/103-cover.jpg',1,1),
(4,'demo/rooms/104-cover.jpg',1,1), (5,'demo/rooms/105-cover.jpg',1,1),
(6,'demo/rooms/106-cover.jpg',1,1), (7,'demo/rooms/201-cover.jpg',1,1),
(8,'demo/rooms/202-cover.jpg',1,1), (9,'demo/rooms/203-cover.jpg',1,1),
(10,'demo/rooms/204-cover.jpg',1,1), (11,'demo/rooms/301-cover.jpg',1,1),
(12,'demo/rooms/302-cover.jpg',1,1);
-- Chu y: cac duong dan anh demo la placeholder; thay bang anh do nhom upload.

INSERT INTO bookings
(id,booking_code,user_id,contact_name,contact_phone,guest_count,
 check_in_date,check_out_date,status,total_amount,reviewed_by_user_id,
 reviewed_at,expires_at,cancelled_at,actual_check_in_at,actual_check_out_at)
VALUES
(1,'BKG-0001',3,'Nguyễn Minh Anh','0901000001',2,'2026-11-06','2026-11-08','confirmed',1100000,2,'2026-10-29 10:00:00',NULL,NULL,NULL,NULL),
(2,'BKG-0002',4,'Trần Hải Nam','0901000002',3,'2026-11-07','2026-11-10','pending_payment',2550000,2,'2026-10-29 11:00:00','2026-11-07 12:00:00',NULL,NULL,NULL),
(3,'BKG-0003',5,'Lê Thu Hà','0901000003',4,'2026-11-12','2026-11-14','pending_approval',2800000,NULL,NULL,'2026-11-12 10:00:00',NULL,NULL,NULL),
(4,'BKG-0004',3,'Nguyễn Minh Anh','0901000001',2,'2026-10-25','2026-10-28','completed',1650000,2,'2026-10-24 09:30:00',NULL,NULL,'2026-10-25 14:05:00','2026-10-28 11:40:00'),
(5,'BKG-0005',4,'Trần Hải Nam','0901000002',4,'2026-11-03','2026-11-05','cancelled',2400000,NULL,NULL,NULL,'2026-10-28 09:00:00',NULL,NULL),
(6,'BKG-0006',5,'Lê Thu Hà','0901000003',3,'2026-10-30','2026-11-01','checked_in',1700000,2,'2026-10-29 08:00:00',NULL,NULL,'2026-10-30 14:00:00',NULL),
(7,'BKG-0007',5,'Lê Thu Hà','0901000003',2,'2026-11-08','2026-11-09','rejected',550000,2,'2026-10-29 15:00:00',NULL,NULL,NULL,NULL),
(8,'BKG-0008',3,'Nguyễn Minh Anh','0901000001',2,'2026-11-15','2026-11-17','pending_payment',1100000,2,'2026-10-30 10:00:00','2026-11-15 12:00:00',NULL,NULL,NULL);

-- room 101: 550k x 2 dem; room 201: 850k x 3 dem, v.v.
INSERT INTO booking_details (booking_id,room_id,price_per_night,line_total) VALUES
(1,1,550000,1100000),
(2,7,850000,2550000),
(3,2,550000,1100000), (3,8,850000,1700000),
(4,3,550000,1650000),
(5,11,1200000,2400000),
(6,9,850000,1700000),
(7,4,550000,550000),
(8,5,550000,1100000);

-- Cac lan thanh toan da co: thanh cong + tu choi + khong du tien.
INSERT INTO payments
(id,booking_id,payment_code,provider,provider_transaction_id,method,amount,status,paid_at)
VALUES
(1,1,'PAY-SAMPLE-0001','MOCK_CARD_GATEWAY','SIM-TXN-0001','online',1100000,'success','2026-10-29 10:20:00'),
(2,2,'PAY-SAMPLE-0002','MOCK_CARD_GATEWAY','SIM-TXN-0002','online',2550000,'failed',NULL),
(3,4,'PAY-SAMPLE-0003','MOCK_CARD_GATEWAY','SIM-TXN-0003','online',1650000,'success','2026-10-24 10:00:00'),
(4,6,'PAY-SAMPLE-0004','MOCK_CARD_GATEWAY','SIM-TXN-0004','online',1700000,'success','2026-10-29 09:15:00'),
(5,2,'PAY-SAMPLE-0005','MOCK_CARD_GATEWAY','SIM-TXN-0005','online',2550000,'failed',NULL);

INSERT INTO card_payment_details
(payment_id,card_brand,card_last4,cardholder_name,simulation_result,authorization_code,processed_at)
VALUES
(1,'VISA','4242','NGUYEN MINH ANH','approved','AUTH-DEMO-01','2026-10-29 10:20:00'),
(2,'MASTERCARD','0002','TRAN HAI NAM','declined',NULL,'2026-10-29 11:20:00'),
(3,'VISA','4242','NGUYEN MINH ANH','approved','AUTH-DEMO-03','2026-10-24 10:00:00'),
(4,'JCB','1234','LE THU HA','approved','AUTH-DEMO-04','2026-10-29 09:15:00'),
(5,'VISA','9995','TRAN HAI NAM','insufficient_funds',NULL,'2026-10-29 11:25:00');



-- ============== 03_thu_tuc_thanh_toan_gia_lap.sql ==============
-- 03_GIA_LAP_THANH_TOAN_THE.SQL
-- Quy tac: 1 lan thu = 1 payments + 1 card_payment_details.
-- Khong nhan so the day du hay CVV. Nhan LOAI, 4 so cuoi, ten GIA, ket qua mo phong.
-- Chi dung cho DEV/DEMO. Khong phai cong thanh toan ngan hang!
USE hotel_mini_card_demo;

DROP PROCEDURE IF EXISTS sp_simulate_card_payment;
DELIMITER $$
CREATE PROCEDURE sp_simulate_card_payment(
  IN p_booking_code VARCHAR(30),
  IN p_card_brand VARCHAR(20),
  IN p_card_last4 CHAR(4),
  IN p_cardholder_name VARCHAR(120),
  IN p_scenario VARCHAR(30)
)
BEGIN
  DECLARE v_booking_id BIGINT UNSIGNED;
  DECLARE v_booking_status VARCHAR(30);
  DECLARE v_amount DECIMAL(13,2);
  DECLARE v_expires_at DATETIME;
  DECLARE v_payment_id BIGINT UNSIGNED;
  DECLARE v_result_status VARCHAR(10);
  DECLARE v_payment_code VARCHAR(40);
  DECLARE v_txn_id VARCHAR(100);
  DECLARE v_missing BOOLEAN DEFAULT FALSE;

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_missing = TRUE;
  DECLARE EXIT HANDLER FOR SQLEXCEPTION
  BEGIN
    ROLLBACK;
    RESIGNAL;
  END;

  IF p_scenario IS NULL OR p_scenario NOT IN ('approved','declined','insufficient_funds') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Scenario must be approved / declined / insufficient_funds';
  END IF;
  IF p_card_brand IS NULL OR UPPER(p_card_brand) NOT IN ('VISA','MASTERCARD','JCB') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid demo card brand';
  END IF;
  IF p_card_last4 IS NULL OR p_card_last4 NOT REGEXP '^[0-9]{4}$' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Use exactly 4 digits of a TEST card';
  END IF;
  IF p_cardholder_name IS NULL OR CHAR_LENGTH(TRIM(p_cardholder_name)) = 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Demo cardholder name required';
  END IF;

  START TRANSACTION;
  SELECT id, status, total_amount, expires_at
    INTO v_booking_id, v_booking_status, v_amount, v_expires_at
  FROM bookings
  WHERE booking_code = p_booking_code
  LIMIT 1 FOR UPDATE;

  IF v_missing THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Booking code not found';
  END IF;
  IF v_booking_status <> 'pending_payment' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Booking is not awaiting payment';
  END IF;
  IF v_expires_at IS NOT NULL AND v_expires_at <= NOW() THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Payment deadline has passed';
  END IF;
  IF v_amount <= 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Booking amount must be positive';
  END IF;
  IF EXISTS(SELECT 1 FROM payments WHERE booking_id=v_booking_id AND status='success') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Booking already paid';
  END IF;

  SET v_result_status = IF(p_scenario='approved','success','failed');
  SET v_payment_code = CONCAT('PAY-',REPLACE(UUID(),'-',''));
  SET v_txn_id = CONCAT('SIM-',REPLACE(UUID(),'-',''));

  INSERT INTO payments (
    booking_id,payment_code,provider,provider_transaction_id,method,amount,status,paid_at
  ) VALUES (
    v_booking_id,v_payment_code,'MOCK_CARD_GATEWAY',v_txn_id,'online',v_amount,
    v_result_status,IF(p_scenario='approved',NOW(),NULL)
  );
  SET v_payment_id=LAST_INSERT_ID();

  INSERT INTO card_payment_details
    (payment_id,card_brand,card_last4,cardholder_name,simulation_result,authorization_code,processed_at)
  VALUES
    (v_payment_id,UPPER(p_card_brand),p_card_last4,TRIM(p_cardholder_name),p_scenario,
     IF(p_scenario='approved',CONCAT('AUTH-',UPPER(LEFT(REPLACE(UUID(),'-',''),10))),NULL),NOW());

  IF p_scenario='approved' THEN
    UPDATE bookings SET status='confirmed', updated_at=CURRENT_TIMESTAMP
    WHERE id=v_booking_id;
  END IF;
  COMMIT;
  SELECT p.id AS payment_id,p.payment_code,p.amount,p.status AS payment_status,
         c.card_brand,c.card_last4,c.simulation_result,b.booking_code,
         b.status AS booking_status
    FROM payments p
    JOIN card_payment_details c ON c.payment_id=p.id
    JOIN bookings b ON b.id=p.booking_id
   WHERE p.id=v_payment_id;
END$$
DELIMITER ;

