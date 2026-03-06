/**
 * APS Dream Home - System Test (Windows Compatible)
 * Tests all optimized systems and functionality
 */

const fs = require('fs');
const path = require('path');

class SystemTester {
    constructor() {
        this.results = [];
        this.startTime = Date.now();
    }

    // Test if files exist
    testFileExists(filePath, description) {
        try {
            if (fs.existsSync(filePath)) {
                this.logSuccess(`${description} exists`);
                return true;
            } else {
                this.logError(`${description} missing`);
                return false;
            }
        } catch (error) {
            this.logError(`${description} error: ${error.message}`);
            return false;
        }
    }

    // Test directory structure
    testDirectoryStructure() {
        console.log('🧪 Testing Directory Structure...');

        const directories = [
            'assets/css',
            'assets/js',
            'includes',
            'admin'
        ];

        let allValid = true;
        directories.forEach(dir => {
            if (!this.testFileExists(dir, `Directory ${dir}`)) {
                allValid = false;
            }
        });

        return allValid;
    }

    // Test template system
    testTemplateSystem() {
        console.log('🧪 Testing Template System...');

        return this.testFileExists('includes/enhanced_universal_template.php', 'Enhanced template system') &&
               this.testFileExists('includes/templates/header.php', 'Template header') &&
               this.testFileExists('includes/templates/footer.php', 'Template footer');
    }

    // Test JavaScript utilities
    testJavaScriptUtilities() {
        console.log('🧪 Testing JavaScript System...');

        return this.testFileExists('assets/js/utils.js', 'JavaScript utils') &&
               this.testFileExists('assets/js/custom.js', 'Custom JavaScript') &&
               this.testFileExists('package.json', 'Package configuration');
    }

    // Test CSS consolidation
    testCSSConsolidation() {
        console.log('🧪 Testing CSS System...');

        const cssFiles = [
            'assets/css/custom-styles.css',
            'assets/css/admin.css',
            'assets/css/style.css'
        ];

        let allValid = true;
        cssFiles.forEach(file => {
            if (!this.testFileExists(file, `CSS file ${path.basename(file)}`)) {
                allValid = false;
            }
        });

        return allValid;
    }

    // Test PHP files
    testPHPFiles() {
        console.log('🧪 Testing PHP Files...');

        const phpFiles = [
            'index.php',
            'homepage.php',
            'admin/admin_panel.php'
        ];

        let allValid = true;
        phpFiles.forEach(file => {
            if (!this.testFileExists(file, `PHP file ${path.basename(file)}`)) {
                allValid = false;
            }
        });

        return allValid;
    }

    // Run all tests
    runAllTests() {
        console.log('🚀 APS Dream Home - System Validation');
        console.log('=' .repeat(50));

        const tests = [
            this.testDirectoryStructure.bind(this),
            this.testTemplateSystem.bind(this),
            this.testCSSConsolidation.bind(this),
            this.testJavaScriptUtilities.bind(this),
            this.testPHPFiles.bind(this)
        ];

        let passedTests = 0;
        let totalTests = tests.length;

        tests.forEach((test, index) => {
            console.log(`\n--- Test ${index + 1}/${totalTests} ---`);
            if (test()) {
                passedTests++;
            }
        });

        const endTime = Date.now();
        const duration = (endTime - this.startTime) / 1000;

        console.log('\n' + '=' .repeat(50));
        console.log('📊 VALIDATION RESULTS');
        console.log('=' .repeat(50));
        console.log(`✅ Tests Passed: ${passedTests}/${totalTests}`);
        console.log(`❌ Tests Failed: ${totalTests - passedTests}/${totalTests}`);
        console.log(`⏱️  Duration: ${duration.toFixed(2)} seconds`);

        if (passedTests === totalTests) {
            console.log('\n🎉 ALL SYSTEMS VALIDATED! Ready for development.');
            console.log('🌐 Development: http://localhost:3000');
            console.log('🔧 PHP Server: http://localhost:8000');
            console.log('📱 PWA Ready: npm run build');
        } else {
            console.log('\n⚠️  Some systems need attention. Check errors above.');
        }

        return passedTests === totalTests;
    }

    // Helper methods
    logSuccess(message) {
        console.log(`  ✅ ${message}`);
        this.results.push({ type: 'success', message });
    }

    logError(message) {
        console.log(`  ❌ ${message}`);
        this.results.push({ type: 'error', message });
    }
}

// Run tests if called directly
if (require.main === module) {
    const tester = new SystemTester();
    const success = tester.runAllTests();
    process.exit(success ? 0 : 1);
}

module.exports = SystemTester;
            } else {
                this.logError('Template system file missing');
                return false;
            }

            // Test template class
            const templateContent = fs.readFileSync(templatePath, 'utf8');
            if (templateContent.includes('class EnhancedUniversalTemplate')) {
                this.logSuccess('Template class found');
            } else {
                this.logError('Template class not found');
                return false;
            }

            return true;
        } catch (error) {
            this.logError(`Template test failed: ${error.message}`);
            return false;
        }
    }

    // Test JavaScript utilities
    testJavaScriptUtilities() {
        console.log('🧪 Testing JavaScript Utilities...');

        try {
            const utilsPath = 'assets/js/utils.js';
            if (fs.existsSync(utilsPath)) {
                this.logSuccess('Utils file exists');
            } else {
                this.logError('Utils file missing');
                return false;
            }

            const utilsContent = fs.readFileSync(utilsPath, 'utf8');

            // Check for key utility functions
            const requiredFunctions = [
                'initAOS',
                'initTooltips',
                'initSmoothScrolling',
                'initLazyLoading',
                'debounce'
            ];

            let allFunctionsFound = true;
            requiredFunctions.forEach(func => {
                if (utilsContent.includes(`export function ${func}`)) {
                    this.logSuccess(`Function ${func} found`);
                } else {
                    this.logError(`Function ${func} missing`);
                    allFunctionsFound = false;
                }
            });

            return allFunctionsFound;
        } catch (error) {
            this.logError(`JavaScript test failed: ${error.message}`);
            return false;
        }
    }

    // Test CSS consolidation
    testCSSConsolidation() {
        console.log('🧪 Testing CSS Consolidation...');

        try {
            const essentialCSS = [
                'assets/css/custom-styles.css',
                'assets/css/admin.css',
                'assets/css/style.css',
                'assets/css/faq.css'
            ];

            let allFilesExist = true;
            essentialCSS.forEach(file => {
                if (fs.existsSync(file)) {
                    this.logSuccess(`CSS file ${path.basename(file)} exists`);
                } else {
                    this.logError(`CSS file ${path.basename(file)} missing`);
                    allFilesExist = false;
                }
            });

            return allFilesExist;
        } catch (error) {
            this.logError(`CSS test failed: ${error.message}`);
            return false;
        }
    }

    // Test file structure integrity
    testFileStructure() {
        console.log('🧪 Testing File Structure...');

        const requiredDirectories = [
            'assets/css',
            'assets/js',
            'includes',
            'admin'
        ];

        let structureValid = true;
        requiredDirectories.forEach(dir => {
            if (fs.existsSync(dir)) {
                this.logSuccess(`Directory ${dir} exists`);
            } else {
                this.logError(`Directory ${dir} missing`);
                structureValid = false;
            }
        });

        return structureValid;
    }

    // Test PHP file syntax
    testPHPSyntax() {
        console.log('🧪 Testing PHP Syntax...');

        const phpFiles = [
            'index.php',
            'homepage.php',
            'properties.php',
            'about.php',
            'contact.php',
            'admin/admin_panel.php'
        ];

        let allValid = true;
        phpFiles.forEach(file => {
            try {
                // Basic syntax check by reading file
                const content = fs.readFileSync(file, 'utf8');
                if (content.includes('<?php')) {
                    this.logSuccess(`PHP file ${path.basename(file)} has valid structure`);
                } else {
                    this.logError(`PHP file ${path.basename(file)} missing PHP tag`);
                    allValid = false;
                }
            } catch (error) {
                this.logError(`PHP file ${path.basename(file)} error: ${error.message}`);
                allValid = false;
            }
        });

        return allValid;
    }

    // Test configuration files
    testConfiguration() {
        console.log('🧪 Testing Configuration...');

        const configFiles = [
            'package.json',
            'vite.config.js',
            '.htaccess'
        ];

        let allValid = true;
        configFiles.forEach(file => {
            if (fs.existsSync(file)) {
                this.logSuccess(`Config file ${path.basename(file)} exists`);
            } else {
                this.logError(`Config file ${path.basename(file)} missing`);
                allValid = false;
            }
        });

        return allValid;
    }

    // Run all tests
    runAllTests() {
        console.log('🚀 APS Dream Home - Comprehensive System Test');
        console.log('=' .repeat(60));

        const tests = [
            this.testFileStructure.bind(this),
            this.testTemplateSystem.bind(this),
            this.testCSSConsolidation.bind(this),
            this.testJavaScriptUtilities.bind(this),
            this.testPHPSyntax.bind(this),
            this.testConfiguration.bind(this)
        ];

        let passedTests = 0;
        let totalTests = tests.length;

        tests.forEach((test, index) => {
            console.log(`\n--- Test ${index + 1}/${totalTests} ---`);
            if (test()) {
                passedTests++;
            }
        });

        const endTime = Date.now();
        const duration = (endTime - this.startTime) / 1000;

        console.log('\n' + '=' .repeat(60));
        console.log('📊 TEST RESULTS SUMMARY');
        console.log('=' .repeat(60));
        console.log(`✅ Tests Passed: ${passedTests}/${totalTests}`);
        console.log(`❌ Tests Failed: ${totalTests - passedTests}/${totalTests}`);
        console.log(`⏱️  Duration: ${duration.toFixed(2)} seconds`);

        if (passedTests === totalTests) {
            console.log('\n🎉 ALL TESTS PASSED! System is ready for production.');
            console.log('🚀 Build command: npm run build');
            console.log('🌐 Development: npm run dev');
        } else {
            console.log('\n⚠️  Some tests failed. Please review the errors above.');
        }

        return passedTests === totalTests;
    }

    // Helper methods
    logSuccess(message) {
        console.log(`  ✅ ${message}`);
        this.results.push({ type: 'success', message });
    }

    logError(message) {
        console.log(`  ❌ ${message}`);
        this.results.push({ type: 'error', message });
    }
}

// Run tests if called directly
if (require.main === module) {
    const tester = new SystemTester();
    const success = tester.runAllTests();
    process.exit(success ? 0 : 1);
}

module.exports = SystemTester;
