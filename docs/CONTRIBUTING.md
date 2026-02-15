# 🤝 Contributing to APS Dream Home

First off, thanks for taking the time to contribute! ❤️

## 📋 Code of Conduct
This project and everyone participating in it is governed by our [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## 🚀 Getting Started
1. Fork the repository on GitHub
2. Clone your fork locally
   ```bash
   git clone https://github.com/your-username/apsdreamhome.git
   cd apsdreamhome
   ```
3. Create a new branch for your changes
   ```bash
   git checkout -b feature/your-feature-name
   ```
4. Make your changes
5. Push your changes to your fork
6. Open a pull request

## 🛠 Development Setup
1. Make sure you have PHP 7.4+ and MySQL 5.7+ installed
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Copy `.env.example` to `.env` and configure your environment
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Run migrations:
   ```bash
   php artisan migrate
   ```

## 📝 Pull Request Process
1. Ensure any install or build dependencies are removed before the end of the layer when doing a build
2. Update the README.md with details of changes to the interface
3. Increase the version numbers in any example files and the README.md to the new version that this Pull Request would represent
4. You may merge the Pull Request once you have the sign-off of two other developers, or if you do not have permission to do that, you may request the reviewer to merge it for you

## 🐛 Found a Bug?
If you find a bug, you can help us by [submitting an issue](#) to our GitHub Repository. Even better, you can submit a Pull Request with a fix.
