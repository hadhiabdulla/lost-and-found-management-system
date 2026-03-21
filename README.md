# 🔍 Lost & Found Management System

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

A comprehensive web-based Lost & Found Management System built with PHP, MySQL, and modern responsive frontend. This system helps institutions, organizations, and communities manage lost and found items efficiently.

**DBMS Lab Mini Project** - Demonstrating database design, relationships, and CRUD operations.

---

## ✨ Features

### 🔐 User Management
- User Registration & Authentication
- Role-based Access Control (Admin & User roles)
- Secure Password Hashing (BCrypt)
- Session Management

### 📝 Item Management
- **Report Lost Items** - Users can report items they've lost
- **Report Found Items** - Users can report items they've found
- Image Upload Support (up to 5MB)
- Category-based Classification (Electronics, Clothing, Documents, etc.)
- Location & Date Tracking

### 🔍 Search & Browse
- Advanced Search & Filtering
- Browse by Categories
- Filter by Status (Open, Resolved, Claimed)
- Real-time Search Results

### 🤝 Matching System
- Auto-matching Algorithm
- Match lost items with found items based on category
- Notification System for potential matches

### 📊 Dashboard & Analytics
- User Dashboard with Personal Items
- Admin Dashboard with System Statistics
- Total Lost Items, Found Items, Resolved Cases
- Pending Claims Management

### 🔔 Notifications
- Real-time Notifications for Matches
- Claim Status Updates
- Unread Notification Counter

---

## 🗄️ Database Schema

The system uses **8 interconnected tables** demonstrating proper database normalization and relationships:

1. **users** - User accounts with authentication
2. **categories** - Item categories
3. **lost_items** - Lost item reports
4. **found_items** - Found item reports
5. **claims** - Claims for found items
6. **matches** - Auto-matched items
7. **notifications** - User notifications
8. **audit_log** - Admin activity tracking

**Key Relationships:**
- One-to-Many: Users ↔ Lost/Found Items
- Many-to-Many: Lost Items ↔ Found Items (via Matches)
- Foreign Keys with CASCADE/SET NULL policies

---

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4+ or PHP 8.x
- MySQL 5.7+ or MariaDB
- Apache/Nginx Web Server
- (Recommended) XAMPP/WAMP/MAMP for local development

### Installation Steps

1. **Clone the Repository**
```bash
git clone https://github.com/hadhiabdulla/lost-and-found-management-system.git
cd lost-and-found-management-system
```

2. **Database Setup**
```bash
# Create database and import schema
mysql -u root -p
```
```sql
source database/schema.sql;
```

Or use phpMyAdmin:
- Create a new database named `lost_found_db`
- Import `database/schema.sql`

3. **Configure Database Connection**

Edit `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Your MySQL username
define('DB_PASS', '');       // Your MySQL password
define('DB_NAME', 'lost_found_db');
```

4. **Set Base URL**

In `config/db.php`, update:
```php
define('BASE_URL', 'http://localhost/lost-and-found-management-system');
```

5. **Create Uploads Directory**
```bash
mkdir uploads
chmod 755 uploads
```

6. **Access the Application**

Open your browser:
```
http://localhost/lost-and-found-management-system/
```

---

## 👤 Default Credentials

**Admin Account:**
- Email: `admin@lostandfound.com`
- Password: `Admin@1234`

---

## 📁 Project Structure

```
lost-and-found-management-system/
│
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet (responsive, modern UI)
│   └── js/
│       └── main.js            # JavaScript utilities
│
├── config/
│   └── db.php                 # Database configuration
│
├── database/
│   └── schema.sql             # Database schema with sample data
│
├── includes/
│   ├── auth.php               # Authentication functions
│   ├── functions.php          # Core business logic
│   ├── navbar.php             # Navigation bar component
│   └── footer.php             # Footer component
│
├── uploads/                   # User-uploaded images
│
├── index.php                  # Home/Dashboard
├── login.php                  # Login page
├── register.php               # Registration page
├── report-lost.php            # Report lost item form
├── report-found.php           # Report found item form
├── browse.php                 # Browse all items
├── item-detail.php            # Item detail view
├── dashboard.php              # User dashboard
├── logout.php                 # Logout handler
└── README.md                  # Documentation
```

---

## 🎨 UI Features

- **Modern, Responsive Design** - Works on all devices
- **Clean Material Design** - Inspired by modern UI principles
- **Smooth Animations** - Hover effects, transitions
- **Color-coded Status** - Visual distinction for item types
- **FontAwesome Icons** - Beautiful iconography
- **Mobile-First Approach** - Optimized for mobile devices

---

## 🔧 Technologies Used

- **Backend:** PHP 8.x
- **Database:** MySQL 8.0
- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Icons:** Font Awesome 6.4
- **Design:** Custom CSS with CSS Variables
- **Security:** Password Hashing, SQL Injection Prevention, XSS Protection

---

## 📝 Key Functionalities

### For Users:
1. Register and Login
2. Report Lost Items with images
3. Report Found Items with location
4. Browse all lost/found items
5. Search and filter items
6. View item details with contact info
7. Get notifications for matches

### For Admins:
1. All user functionalities
2. View system statistics
3. Manage all items
4. Review and approve claims
5. Access audit logs
6. Manage users

---

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- Prepared statements to prevent SQL injection
- Input sanitization and validation
- XSS protection with `htmlspecialchars()`
- Session-based authentication
- Role-based access control
- File upload restrictions (type & size)

---

## 📊 Database Features Demonstrated

✅ **Normalization** - Tables follow 3NF
✅ **Primary Keys** - Auto-increment IDs
✅ **Foreign Keys** - Referential integrity
✅ **ENUM Types** - For status fields
✅ **Timestamps** - Automatic date tracking
✅ **ON DELETE CASCADE** - Cascading deletes
✅ **ON UPDATE CASCADE** - Cascading updates
✅ **Indexes** - Email uniqueness
✅ **JOINs** - Complex queries with multiple tables
✅ **Aggregate Functions** - COUNT, statistics

---

## 🎯 Future Enhancements

- [ ] Email Notifications
- [ ] SMS Integration
- [ ] Advanced AI-based Matching
- [ ] QR Code Generation for Items
- [ ] Multi-language Support
- [ ] PWA (Progressive Web App)
- [ ] Mobile App (React Native/Flutter)
- [ ] Export Reports (PDF/Excel)

---

## 📸 Screenshots

*Add screenshots of your application here*

---

## 👨‍💻 Author

**Hadhi Abdulla**  
Computer Science & Engineering (Cybersecurity)  
DBMS Lab Mini Project

---

## 📄 License

This project is created for educational purposes as part of DBMS Lab coursework.

---

## 🤝 Contributing

This is an academic project. Feel free to fork and modify for your own learning purposes.

---

## 📞 Support

For any queries regarding this project, please open an issue in the repository.

---

**⭐ If you find this project useful, please star the repository!**
