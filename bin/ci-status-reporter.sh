#!/bin/bash

# CI Status Reporter for SkillPulse LMS
# Provides detailed status reporting for GitHub Actions

set -e

# Function to create status badge
create_status_badge() {
    local status="$1"
    local label="$2"

    case "$status" in
        "success" | "passed")
            echo "✅ $label: PASSED"
            ;;
        "failure" | "failed")
            echo "❌ $label: FAILED"
            ;;
        "cancelled")
            echo "⏹️ $label: CANCELLED"
            ;;
        "skipped")
            echo "⏭️ $label: SKIPPED"
            ;;
        *)
            echo "⚠️ $label: UNKNOWN"
            ;;
    esac
}

# Function to generate test results table
generate_test_results_table() {
    local phpunit_result="$1"
    local code_quality_result="$2"
    local security_result="$3"

    echo "| Component | Status | Details |"
    echo "|-----------|--------|---------|"

    # PHPUnit Results
    case "$phpunit_result" in
        "success")
            echo "| PHPUnit Tests | ✅ PASSED | All unit and integration tests passed |"
            ;;
        "failure")
            echo "| PHPUnit Tests | ❌ FAILED | Some tests failed - check logs for details |"
            ;;
        *)
            echo "| PHPUnit Tests | ⚠️ UNKNOWN | Test status could not be determined |"
            ;;
    esac

    # Code Quality Results
    case "$code_quality_result" in
        "success")
            echo "| Code Quality (PHPCS) | ✅ PASSED | All files follow WordPress coding standards |"
            echo "| Code Quality (ESLint) | ✅ PASSED | All JavaScript files follow coding standards |"
            ;;
        "failure")
            echo "| Code Quality (PHPCS) | ❌ FAILED | Coding standard violations found |"
            echo "| Code Quality (ESLint) | ❌ FAILED | JavaScript linting issues found |"
            ;;
        *)
            echo "| Code Quality | ⚠️ UNKNOWN | Code quality status could not be determined |"
            ;;
    esac

    # Security Results
    case "$security_result" in
        "success")
            echo "| Security Scan | ✅ PASSED | No known vulnerabilities found |"
            ;;
        "failure")
            echo "| Security Scan | ❌ FAILED | Security vulnerabilities detected |"
            ;;
        *)
            echo "| Security Scan | ⚠️ UNKNOWN | Security scan status could not be determined |"
            ;;
    esac
}

# Function to generate matrix results table
generate_matrix_results_table() {
    local overall_result="$1"
    shift  # Remove first argument
    local matrix_results=("$@")  # Get remaining arguments as array

    echo "| PHP Version | WordPress Version | Status | Notes |"
    echo "|-------------|-------------------|--------|-------|"

    # Generate matrix combinations
    php_versions=("7.4" "8.0" "8.1" "8.2")
    wp_versions=("5.9" "6.0" "latest")
    combination_index=0

    for php in "${php_versions[@]}"; do
        for wp in "${wp_versions[@]}"; do
            # Skip combinations that don't exist in our matrix
            if [[ "$php" != "7.4" && "$wp" == "5.9" ]]; then
                continue
            fi

            local status_icon=""
            local notes=""

            # Use individual matrix result if provided, otherwise use overall result
            local matrix_result="$overall_result"
            if [[ $combination_index -lt ${#matrix_results[@]} ]]; then
                matrix_result="${matrix_results[$combination_index]}"
            fi

            case "$matrix_result" in
                "success")
                    status_icon="✅ PASSED"
                    ;;
                "failure")
                    status_icon="❌ FAILED"
                    ;;
                "cancelled")
                    status_icon="⏹️ CANCELLED"
                    ;;
                "skipped")
                    status_icon="⏭️ SKIPPED"
                    ;;
                *)
                    status_icon="⚠️ UNKNOWN"
                    ;;
            esac

            # Special notes for coverage builds
            if [[ "$php" == "8.1" && "$wp" == "latest" ]]; then
                notes="Includes coverage report"
            else
                notes="Standard test run"
            fi

            echo "| $php | $wp | $status_icon | $notes |"
            ((combination_index++))
        done
    done
}

# Function to generate recommendations based on failures
generate_recommendations() {
    local phpunit_result="$1"
    local code_quality_result="$2"
    local security_result="$3"

    echo ""
    echo "## 🔧 Recommendations"

    if [[ "$phpunit_result" == "failure" ]]; then
        echo "### PHPUnit Test Failures"
        echo "- Review test logs for specific failure details"
        echo "- Check if WordPress test environment is properly configured"
        echo "- Verify database connection and table creation"
        echo "- Run tests locally: \`composer test\`"
        echo ""
    fi

    if [[ "$code_quality_result" == "failure" ]]; then
        echo "### Code Quality Issues"
        echo "- Fix PHPCS violations: \`composer cs:fix\`"
        echo "- Review ESLint errors and fix JavaScript issues"
        echo "- Download PHPCS report artifact for detailed violations"
        echo "- Ensure all files follow WordPress coding standards"
        echo ""
    fi

    if [[ "$security_result" == "failure" ]]; then
        echo "### Security Vulnerabilities"
        echo "- Update vulnerable PHP dependencies: \`composer update\`"
        echo "- Update vulnerable Node dependencies: \`npm audit fix\`"
        echo "- Review security audit details in job logs"
        echo "- Consider using alternative packages if updates aren't available"
        echo ""
    fi

    # General recommendations
    echo "### General Next Steps"
    echo "1. 📋 Review the detailed logs for each failed job"
    echo "2. 📁 Download artifacts for detailed reports (PHPCS, coverage)"
    echo "3. 🔧 Fix issues locally and test before pushing"
    echo "4. 📤 Push fixes to trigger new CI run"
    echo "5. 📧 Consider setting up notifications for CI failures"
}

# Main function
main() {
    local phpunit_result="${1:-unknown}"
    local code_quality_result="${2:-unknown}"
    local security_result="${3:-unknown}"

    echo "# 📊 SkillPulse LMS CI/CD Results"
    echo ""
    echo "Generated on: $(date -u '+%Y-%m-%d %H:%M:%S UTC')"
    echo ""

    # Overall status
    local overall_status="✅ SUCCESS"
    if [[ "$phpunit_result" == "failure" || "$code_quality_result" == "failure" || "$security_result" == "failure" ]]; then
        overall_status="❌ FAILURE"
    fi

    echo "**Overall Status: $overall_status**"
    echo ""

    # Test results table
    echo "## 📋 Test Results Summary"
    generate_test_results_table "$phpunit_result" "$code_quality_result" "$security_result"
    echo ""

    # Matrix results
    echo "## 🔄 Matrix Build Results"
    generate_matrix_results_table "$phpunit_result"
    echo ""

    # Generate recommendations if there are failures
    if [[ "$overall_status" == "❌ FAILURE" ]]; then
        generate_recommendations "$phpunit_result" "$code_quality_result" "$security_result"
    else
        echo "## 🎉 All Tests Passed!"
        echo ""
        echo "Great job! All automated checks have passed successfully:"
        echo "- ✅ Unit and integration tests are working"
        echo "- ✅ Code follows WordPress and JavaScript standards"
        echo "- ✅ No security vulnerabilities detected"
        echo ""
        echo "The code is ready for deployment! 🚀"
    fi

    # Additional information
    echo ""
    echo "---"
    echo "*This report was generated automatically by the SkillPulse LMS CI/CD pipeline.*"
    echo ""
    echo "For more details:"
    echo "- 📖 [Testing Documentation](./phpunittest.md)"
    echo "- 🔧 [CI/CD Setup Guide](./CI-CD-STATUS.md)"
    echo "- 📝 [Latest Fixes Applied](./CI-CD-FIXES.md)"
}

# Execute if called directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi