# CashBook

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/license/mit/)  
[![Laravel Version](https://img.shields.io/badge/Laravel-12-orange)](https://laravel.com/)  
[![Vue.js Version](https://img.shields.io/badge/Vue.js-3-blue)](https://vuejs.org/)  
[![Bootstrap Version](https://img.shields.io/badge/Bootstrap-5-purple)](https://getbootstrap.com/)  
[![GitHub Stars](https://img.shields.io/github/stars/Riaz-Mahmud/cashbook)](https://github.com/Riaz-Mahmud/cashbook/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/Riaz-Mahmud/cashbook)](https://github.com/Riaz-Mahmud/cashbook/network/members)
[![Issues](https://img.shields.io/github/issues/Riaz-Mahmud/cashbook)](https://github.com/Riaz-Mahmud/cashbook/issues)

**CashBook** is a free, open-source cash management web application for individuals and small businesses. Track cash flow, manage multiple businesses, categorize transactions, and generate insightful financial reports completely free.

---

## 🌟 Features

- Multi-business support: manage multiple businesses from one account  
- Multiple cash books per business  
- Track income and expenses with categories  
- Transaction management with optional receipt uploads  
- Interactive dashboards and analytics  
- User roles and permissions for team management  
- Secure authentication and account management (Laravel Sanctum)  
- PDF export and reporting  
- Responsive, modern UI for all devices  

---

## 🛠 Technologies

- **Backend:** Laravel 10  
- **Frontend:** Vue.js 3, Bootstrap 5  
- **Database:** MySQL  
- **Authentication:** Laravel Sanctum  
- **Storage:** Local file storage for receipts  
- **Development Tools:** Composer, NPM, Webpack  
- **Testing:** PHPUnit, Laravel Dusk  

---

## 🚀 Getting Started

### Prerequisites

- PHP >= 8.1  
- Composer  
- Node.js & NPM  
- MySQL or compatible database  

### Installation

1. Clone the repository:  
```bash
git clone https://github.com/Riaz-Mahmud/cashbook.git
cd cashbook
```

2. Install dependencies:  
```bash
composer install
npm install && npm run build
```

3. Configure environment:  
```bash
cp .env.example .env
php artisan key:generate
```

4. Run migrations and seed the database:  
```bash
php artisan migrate --seed
```

5. Start the development server:  
```bash
php artisan serve
```

Visit `http://127.0.0.1:8000` in your browser.

---

## 📸 Screenshots

![Dashboard](https://github.com/user-attachments/assets/1d503947-6c5b-4904-a58f-162a020617da)  
![Transactions](https://github.com/user-attachments/assets/4036e8ce-55a6-4894-86b1-497dba90bd77)  
![Analytics](https://github.com/user-attachments/assets/967509e8-5127-47b4-9c9e-443f2f7d8d4e)  
![Reports](https://github.com/user-attachments/assets/f60402bc-5337-4534-9a0e-cebecf65f0a7)  
![Settings](https://github.com/user-attachments/assets/063a9c74-800b-4697-a7b0-eaf3af1ce563)  

---

## 🤝 Contributing

CashBook is open-source and welcomes contributions!  

**How to contribute:**

1. Fork the repository  
2. Create a feature branch: `git checkout -b feature/YourFeature`  
3. Commit your changes: `git commit -am 'Add some feature'`  
4. Push to the branch: `git push origin feature/YourFeature`  
5. Open a Pull Request  

Please ensure your code is well-documented and tested before submitting.

---

## 📄 License

This project is licensed under the [MIT License](https://opensource.org/license/mit/).

---

## 👤 Authors

- [Riaz Mahmud](https://github.com/Riaz-Mahmud) - Initial Work

---

## 🙏 Acknowledgements

- [Laravel](https://laravel.com/) – PHP framework  
- [Vue.js](https://vuejs.org/) – JavaScript framework  
- [Bootstrap](https://getbootstrap.com/) – Responsive UI framework  
