#!/usr/bin/env python3
"""
Database setup script - Creates all tables and seeds dummy data
"""
import mysql.connector
from mysql.connector import Error
from datetime import datetime, timedelta
import json
import sys
import io

# Fix encoding for Windows console
if sys.platform == 'win32':
    import codecs
    sys.stdout = codecs.getwriter('utf-8')(sys.stdout.buffer, 'strict')

# Database connection configuration
config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'internship_website',
    'port': 3306,
}

def create_connection():
    """Create a database connection to MySQL"""
    try:
        connection = mysql.connector.connect(**config)
        return connection
    except Error as e:
        print(f"Error while connecting to MySQL: {e}")
        return None

def execute_query(connection, query):
    """Execute a single query"""
    try:
        cursor = connection.cursor()
        cursor.execute(query)
        connection.commit()
        print(f"Query executed successfully")
        return cursor
    except Error as e:
        print(f"Error executing query: {e}")
        connection.rollback()
        return None

def setup_database():
    """Create all database tables"""
    connection = create_connection()
    if not connection:
        return False

    try:
        cursor = connection.cursor()
        
        # Enable foreign key checks
        cursor.execute("SET FOREIGN_KEY_CHECKS=1")
        connection.commit()

        # Create USERS table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                email_verified_at TIMESTAMP NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT 'requester',
                department VARCHAR(100),
                remember_token VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        """)
        print("[OK] Users table created")

        # Create VENDORS table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS vendors (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                location VARCHAR(255),
                contact VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        """)
        print("[OK] Vendors table created")

        # Create PURCHASE_REQUESTS table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS purchase_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                document_number VARCHAR(50) UNIQUE NOT NULL,
                title VARCHAR(255) NOT NULL,
                department VARCHAR(100),
                priority VARCHAR(50) DEFAULT 'normal',
                plant VARCHAR(100),
                need_date DATE,
                note LONGTEXT,
                status VARCHAR(50) DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        """)
        print("[OK] Purchase Requests table created")

        # Create PURCHASE_REQUEST_ITEMS table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS purchase_request_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                purchase_request_id INT NOT NULL,
                item_code VARCHAR(50),
                name VARCHAR(255) NOT NULL,
                quantity INT,
                unit VARCHAR(50),
                specification LONGTEXT,
                note LONGTEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (purchase_request_id) REFERENCES purchase_requests(id) ON DELETE CASCADE
            )
        """)
        print("[OK] Purchase Request Items table created")

        # Create RFQS table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS rfqs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                purchase_request_id INT NOT NULL,
                vendor_id INT,
                note LONGTEXT,
                status VARCHAR(50) DEFAULT 'open',
                opened_at TIMESTAMP NULL,
                closed_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (purchase_request_id) REFERENCES purchase_requests(id) ON DELETE CASCADE,
                FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE SET NULL
            )
        """)
        print("[OK] RFQs table created")

        # Create QUOTATIONS table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS quotations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                rfq_id INT NOT NULL,
                vendor_id INT,
                total_price DECIMAL(16,2),
                note LONGTEXT,
                status VARCHAR(50) DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (rfq_id) REFERENCES rfqs(id) ON DELETE CASCADE,
                FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE SET NULL
            )
        """)
        print("[OK] Quotations table created")

        # Create QUOTATION_PERIODS table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS quotation_periods (
                id INT AUTO_INCREMENT PRIMARY KEY,
                rfq_id INT NOT NULL,
                round INT,
                start_date DATE,
                end_date DATE,
                status VARCHAR(50) DEFAULT 'open',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (rfq_id) REFERENCES rfqs(id) ON DELETE CASCADE
            )
        """)
        print("[OK] Quotation Periods table created")

        # Create VENDOR_QUOTATIONS table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS vendor_quotations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                rfq_id INT NOT NULL,
                vendor_id INT NOT NULL,
                quotation_file VARCHAR(255),
                notes LONGTEXT,
                status VARCHAR(50) DEFAULT 'draft',
                submitted_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (rfq_id) REFERENCES rfqs(id) ON DELETE CASCADE,
                FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE
            )
        """)
        print("[OK] Vendor Quotations table created")

        # Create QUOTATION_DETAILS table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS quotation_details (
                id INT AUTO_INCREMENT PRIMARY KEY,
                quotation_id INT NOT NULL,
                purchase_request_item_id INT NOT NULL,
                offered_price_per_item DECIMAL(16,2),
                offered_quantity INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE,
                FOREIGN KEY (purchase_request_item_id) REFERENCES purchase_request_items(id) ON DELETE CASCADE
            )
        """)
        print("[OK] Quotation Details table created")

        # Create QUOTATION_SUMMARIES table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS quotation_summaries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                rfq_id INT NOT NULL,
                quotation_detail_id INT NOT NULL,
                is_sent_to_user BOOLEAN DEFAULT FALSE,
                sent_to_user_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (rfq_id) REFERENCES rfqs(id) ON DELETE CASCADE,
                FOREIGN KEY (quotation_detail_id) REFERENCES quotation_details(id) ON DELETE CASCADE
            )
        """)
        print("[OK] Quotation Summaries table created")

        # Create VENDOR_SELECTIONS table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS vendor_selections (
                id INT AUTO_INCREMENT PRIMARY KEY,
                rfq_id INT NOT NULL,
                vendor_id INT NOT NULL,
                quotation_id INT NOT NULL,
                decision_notes LONGTEXT,
                decided_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (rfq_id) REFERENCES rfqs(id) ON DELETE CASCADE,
                FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
                FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE
            )
        """)
        print("[OK] Vendor Selections table created")

        # Create SELECTION_ITEMS table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS selection_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                vendor_selection_id INT NOT NULL,
                quotation_summary_id INT NOT NULL,
                final_price_per_item DECIMAL(16,2),
                final_quantity INT,
                notes LONGTEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (vendor_selection_id) REFERENCES vendor_selections(id) ON DELETE CASCADE,
                FOREIGN KEY (quotation_summary_id) REFERENCES quotation_summaries(id) ON DELETE CASCADE
            )
        """)
        print("[OK] Selection Items table created")

        # Create HISTORY table
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                vendor_id INT,
                rfq_id INT,
                vendor_selection_id INT,
                action VARCHAR(255),
                transaction_status VARCHAR(50) DEFAULT 'pending',
                notes LONGTEXT,
                action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE SET NULL,
                FOREIGN KEY (rfq_id) REFERENCES rfqs(id) ON DELETE SET NULL,
                FOREIGN KEY (vendor_selection_id) REFERENCES vendor_selections(id) ON DELETE SET NULL
            )
        """)
        print("[OK] History table created")

        connection.commit()
        print("\n[OK] All tables created successfully!")
        return True

    except Error as e:
        print(f"Error creating tables: {e}")
        return False
    finally:
        cursor.close()

def seed_database():
    """Seed dummy data into the database"""
    connection = create_connection()
    if not connection:
        return False

    try:
        cursor = connection.cursor()

        # Check if data already exists
        cursor.execute("SELECT COUNT(*) FROM users")
        if cursor.fetchone()[0] > 0:
            print("\n[OK] Database already seeded with data")
            return True

        now = datetime.now()
        
        # Insert Users
        users_data = [
            ("Admin Purchasing", "admin@purchasing.local", "purchasing", "Procurement"),
            ("Requestor User", "requester@company.local", "requester", "Operations"),
            ("John Smith", "john.smith@company.local", "requester", "Engineering"),
            ("Sarah Johnson", "sarah.johnson@company.local", "requester", "Maintenance"),
        ]
        
        user_ids = []
        for name, email, role, dept in users_data:
            cursor.execute("""
                INSERT INTO users (name, email, password, role, department)
                VALUES (%s, %s, %s, %s, %s)
            """, (name, email, '$2y$12$abcdefghijklmnopqrstuvwxyz', role, dept))
            user_ids.append(cursor.lastrowid)
        
        connection.commit()
        print("[OK] Seeded 4 Users")

        # Insert Vendors
        vendors_data = [
            ("PT Sumber Mandiri", "Jakarta", "021-5551234"),
            ("PT Prima Niaga", "Bandung", "022-5559876"),
            ("CV Karya Teknik", "Surabaya", "031-5554321"),
        ]
        
        vendor_ids = []
        for name, location, contact in vendors_data:
            cursor.execute("""
                INSERT INTO vendors (name, location, contact)
                VALUES (%s, %s, %s)
            """, (name, location, contact))
            vendor_ids.append(cursor.lastrowid)
        
        connection.commit()
        print("[OK] Seeded 3 Vendors")

        # Insert Purchase Requests
        pr_data = [
            (user_ids[0], "PR-2026-001", "Office Supplies Procurement", "Operations", "high", "Plant A", 
             (now + timedelta(days=15)).date(), "Urgent office supplies needed"),
            (user_ids[1], "PR-2026-002", "Industrial Equipment Purchase", "Engineering", "normal", "Plant B",
             (now + timedelta(days=30)).date(), "Equipment for production line upgrade"),
            (user_ids[2], "PR-2026-003", "Maintenance Tools and Parts", "Maintenance", "normal", "Plant A",
             (now + timedelta(days=20)).date(), "Replacement parts for machinery"),
        ]
        
        pr_ids = []
        for user_id, doc_num, title, dept, priority, plant, need_date, note in pr_data:
            cursor.execute("""
                INSERT INTO purchase_requests 
                (user_id, document_number, title, department, priority, plant, need_date, note, status)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, (user_id, doc_num, title, dept, priority, plant, need_date, note, "open"))
            pr_ids.append(cursor.lastrowid)
        
        connection.commit()
        print("[OK] Seeded 3 Purchase Requests")

        # Insert Purchase Request Items
        pri_data = [
            (pr_ids[0], "OFF-001", "A4 Paper Reams", 50, "Ream", "80 GSM, White", "Standard office paper"),
            (pr_ids[0], "OFF-002", "Ball Pen Set", 100, "Box", "Blue ink, 0.7mm", "For office use"),
            (pr_ids[1], "IND-001", "Electric Motor", 5, "Unit", "2.2 kW, 380V, 3-phase", "Replacement motors"),
            (pr_ids[2], "MNT-001", "Hydraulic Oil", 200, "Liter", "ISO VG 46", "Equipment maintenance"),
            (pr_ids[2], "MNT-002", "Bearing Set", 10, "Set", "SKF Deep Groove Ball Bearings", "Machinery repair"),
        ]
        
        pri_ids = []
        for pr_id, item_code, name, qty, unit, spec, note in pri_data:
            cursor.execute("""
                INSERT INTO purchase_request_items 
                (purchase_request_id, item_code, name, quantity, unit, specification, note)
                VALUES (%s, %s, %s, %s, %s, %s, %s)
            """, (pr_id, item_code, name, qty, unit, spec, note))
            pri_ids.append(cursor.lastrowid)
        
        connection.commit()
        print("[OK] Seeded 5 Purchase Request Items")

        # Insert RFQs
        rfq_data = [
            (pr_ids[0], None, "Request for quotation for office supplies", "open", now, None),
            (pr_ids[1], None, "Equipment procurement RFQ", "open", now, None),
            (pr_ids[2], None, "Maintenance supplies RFQ", "closed", now - timedelta(days=10), now - timedelta(days=2)),
        ]
        
        rfq_ids = []
        for pr_id, vendor_id, note, status, opened_at, closed_at in rfq_data:
            cursor.execute("""
                INSERT INTO rfqs (purchase_request_id, vendor_id, note, status, opened_at, closed_at)
                VALUES (%s, %s, %s, %s, %s, %s)
            """, (pr_id, vendor_id, note, status, opened_at, closed_at))
            rfq_ids.append(cursor.lastrowid)
        
        connection.commit()
        print("[OK] Seeded 3 RFQs")

        # Insert Quotation Periods
        qp_data = [
            (rfq_ids[0], 1, now.date(), (now + timedelta(days=7)).date(), "open"),
            (rfq_ids[1], 1, now.date(), (now + timedelta(days=10)).date(), "open"),
            (rfq_ids[2], 1, (now - timedelta(days=10)).date(), (now - timedelta(days=3)).date(), "closed"),
        ]
        
        for rfq_id, round_num, start_date, end_date, status in qp_data:
            cursor.execute("""
                INSERT INTO quotation_periods (rfq_id, round, start_date, end_date, status)
                VALUES (%s, %s, %s, %s, %s)
            """, (rfq_id, round_num, start_date, end_date, status))
        
        connection.commit()
        print("[OK] Seeded 3 Quotation Periods")

        # Insert Quotations
        quot_data = [
            (rfq_ids[0], vendor_ids[0], 1500000.00, "Best price for office supplies", "submitted"),
            (rfq_ids[0], vendor_ids[1], 1600000.00, "Quick delivery available", "submitted"),
            (rfq_ids[1], vendor_ids[2], 25000000.00, "Equipment with warranty", "submitted"),
            (rfq_ids[2], vendor_ids[0], 5000000.00, "Maintenance supplies bundle", "submitted"),
            (rfq_ids[2], vendor_ids[1], 4800000.00, "Competitive maintenance package", "submitted"),
        ]
        
        quot_ids = []
        for rfq_id, vendor_id, total_price, note, status in quot_data:
            cursor.execute("""
                INSERT INTO quotations (rfq_id, vendor_id, total_price, note, status)
                VALUES (%s, %s, %s, %s, %s)
            """, (rfq_id, vendor_id, total_price, note, status))
            quot_ids.append(cursor.lastrowid)
        
        connection.commit()
        print("[OK] Seeded 5 Quotations")

        # Insert Vendor Quotations
        vq_data = [
            (rfq_ids[0], vendor_ids[0], "quotation_vendor1_rfq1.pdf", "Standard quotation with delivery", "submitted", now - timedelta(days=2)),
            (rfq_ids[0], vendor_ids[1], "quotation_vendor2_rfq1.pdf", "Express delivery option", "submitted", now - timedelta(days=1)),
            (rfq_ids[1], vendor_ids[2], "quotation_vendor3_rfq2.pdf", "Equipment with full warranty", "submitted", now),
        ]
        
        for rfq_id, vendor_id, file, notes, status, submitted_at in vq_data:
            cursor.execute("""
                INSERT INTO vendor_quotations (rfq_id, vendor_id, quotation_file, notes, status, submitted_at)
                VALUES (%s, %s, %s, %s, %s, %s)
            """, (rfq_id, vendor_id, file, notes, status, submitted_at))
        
        connection.commit()
        print("[OK] Seeded 3 Vendor Quotations")

        # Insert Quotation Details
        qd_data = [
            (quot_ids[0], pri_ids[0], 30000.00, 50),
            (quot_ids[0], pri_ids[1], 9000.00, 100),
            (quot_ids[1], pri_ids[0], 32000.00, 50),
            (quot_ids[2], pri_ids[2], 5000000.00, 5),
            (quot_ids[3], pri_ids[3], 25000.00, 200),
        ]
        
        qd_ids = []
        for quot_id, pri_id, price, qty in qd_data:
            cursor.execute("""
                INSERT INTO quotation_details (quotation_id, purchase_request_item_id, offered_price_per_item, offered_quantity)
                VALUES (%s, %s, %s, %s)
            """, (quot_id, pri_id, price, qty))
            qd_ids.append(cursor.lastrowid)
        
        connection.commit()
        print("[OK] Seeded 5 Quotation Details")

        # Insert Quotation Summaries
        qs_data = [
            (rfq_ids[0], qd_ids[0], True, now),
            (rfq_ids[0], qd_ids[1], True, now),
            (rfq_ids[1], qd_ids[3], True, now),
            (rfq_ids[2], qd_ids[4], True, now - timedelta(days=2)),
        ]
        
        qs_ids = []
        for rfq_id, qd_id, is_sent, sent_at in qs_data:
            cursor.execute("""
                INSERT INTO quotation_summaries (rfq_id, quotation_detail_id, is_sent_to_user, sent_to_user_at)
                VALUES (%s, %s, %s, %s)
            """, (rfq_id, qd_id, is_sent, sent_at))
            qs_ids.append(cursor.lastrowid)
        
        connection.commit()
        print("[OK] Seeded 4 Quotation Summaries")

        # Insert Vendor Selections
        vs_data = [
            (rfq_ids[0], vendor_ids[0], quot_ids[0], "Selected for best price and quality", now - timedelta(days=1)),
            (rfq_ids[2], vendor_ids[1], quot_ids[4], "Competitive price with good delivery", now - timedelta(days=2)),
            (rfq_ids[1], vendor_ids[2], quot_ids[2], "Only qualified supplier", now),
        ]
        
        vs_ids = []
        for rfq_id, vendor_id, quot_id, notes, decided_at in vs_data:
            cursor.execute("""
                INSERT INTO vendor_selections (rfq_id, vendor_id, quotation_id, decision_notes, decided_at)
                VALUES (%s, %s, %s, %s, %s)
            """, (rfq_id, vendor_id, quot_id, notes, decided_at))
            vs_ids.append(cursor.lastrowid)
        
        connection.commit()
        print("[OK] Seeded 3 Vendor Selections")

        # Insert Selection Items
        si_data = [
            (vs_ids[0], qs_ids[0], 30000.00, 50, "Confirmed for delivery next week"),
            (vs_ids[0], qs_ids[1], 9000.00, 100, "Standard pens, good quality"),
            (vs_ids[1], qs_ids[3], 25000.00, 200, "Hydraulic oil for machinery"),
            (vs_ids[2], qs_ids[2], 5000000.00, 5, "Electric motors with warranty"),
        ]
        
        for vs_id, qs_id, price, qty, notes in si_data:
            cursor.execute("""
                INSERT INTO selection_items (vendor_selection_id, quotation_summary_id, final_price_per_item, final_quantity, notes)
                VALUES (%s, %s, %s, %s, %s)
            """, (vs_id, qs_id, price, qty, notes))
        
        connection.commit()
        print("[OK] Seeded 4 Selection Items")

        # Insert History
        hist_data = [
            (user_ids[0], vendor_ids[0], rfq_ids[0], vs_ids[0], "PR Created", "completed", "Purchase request PR-2026-001 created", now - timedelta(days=10)),
            (user_ids[3], vendor_ids[0], rfq_ids[0], vs_ids[0], "RFQ Created", "completed", "RFQ created for PR-2026-001", now - timedelta(days=9)),
            (user_ids[3], vendor_ids[0], rfq_ids[0], vs_ids[0], "Vendor Selected", "completed", "PT Sumber Mandiri selected as winner", now - timedelta(days=1)),
            (user_ids[1], vendor_ids[2], rfq_ids[1], vs_ids[2], "PR Created", "completed", "PR-2026-002 created for equipment", now - timedelta(days=8)),
            (user_ids[2], vendor_ids[1], rfq_ids[2], vs_ids[1], "PR Created", "completed", "PR-2026-003 created for maintenance", now - timedelta(days=12)),
        ]
        
        for user_id, vendor_id, rfq_id, vs_id, action, status, notes, action_date in hist_data:
            cursor.execute("""
                INSERT INTO history (user_id, vendor_id, rfq_id, vendor_selection_id, action, transaction_status, notes, action_date)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
            """, (user_id, vendor_id, rfq_id, vs_id, action, status, notes, action_date))
        
        connection.commit()
        print("[OK] Seeded 5 History Records")

        print("\n[OK] All dummy data seeded successfully!")
        print("\n" + "="*50)
        print("SUMMARY:")
        print("="*50)
        print("[OK] 4 Users")
        print("[OK] 3 Vendors")
        print("[OK] 3 Purchase Requests")
        print("[OK] 5 Purchase Request Items")
        print("[OK] 3 RFQs")
        print("[OK] 3 Quotation Periods")
        print("[OK] 3 Vendor Quotations")
        print("[OK] 5 Quotations")
        print("[OK] 5 Quotation Details")
        print("[OK] 4 Quotation Summaries")
        print("[OK] 3 Vendor Selections")
        print("[OK] 4 Selection Items")
        print("[OK] 5 History Records")
        print("="*50)
        
        return True

    except Error as e:
        print(f"Error seeding data: {e}")
        connection.rollback()
        return False
    finally:
        cursor.close()

def main():
    """Main function to setup and seed the database"""
    print("Starting database setup and seeding...")
    print("="*50)
    
    if setup_database():
        if seed_database():
            print("\n[OK] Database setup completed successfully!")
            return 0
    
    print("\n[ERROR] Database setup failed!")
    return 1

if __name__ == "__main__":
    exit(main())
