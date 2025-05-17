# CosyPOS

A full-featured Restaurant Point-of-Sale (POS) web app.

- **Frontend:** HTML, CSS, JS (no frameworks)
- **Backend:** PHP (REST-style endpoints)
- **Database:** MySQL (XAMPP, phpMyAdmin)

---

## 🚀 Setup

1. **Database**
   - Import `schema.sql` using phpMyAdmin (creates DB, tables, test data).

2. **Files**
   - Place all files in `htdocs/cosypos/` (your XAMPP web root).

3. **Run Locally**
   - Start Apache & MySQL in XAMPP.
   - Open: [http://localhost/cosypos/](http://localhost/cosypos/)

---

## 📂 Structure

```
cosypos/
├── api/
│   ├── db.php
│   ├── get_menu.php
│   ├── create_order.php
│   ├── reservations.php
│   ├── tables.php
│   ├── reports.php
│   ├── settings.php
│   └── ...
├── assets/
│   └── style.css
├── js/
│   └── main.js
├── index.html
├── schema.sql
└── README.md
```

---

## ✨ Features

- POS Menu, Order summary, and payment (Cash/Card/E-Wallet)
- Table assignment & management
- Reservation system
- Sales & popular items reports
- Menu & category management (CRUD)
- Modern dark, responsive UI

---

## ⚙️ Customization

- Edit `api/db.php` for DB credentials if not default.
- Tax rate, categories, items editable via Settings.

---

## 👩‍💻 Extending

- Add authentication, multi-user support, or more analytics as needed.

---

Enjoy!  