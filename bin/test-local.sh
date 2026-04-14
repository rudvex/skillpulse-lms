#!/bin/bash

# SkillPulse LMS Local Testing Script
# This script provides unified testing for both wp-env and manual setups

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_color() {
    color=$1
    shift
    echo -e "${color}$@${NC}"
}

# Function to show usage
usage() {
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  --env=<environment>    Set test environment: wp-env|manual|docker (default: auto-detect)"
    echo "  --suite=<suite>        Test suite: unit|integration|all (default: all)"
    echo "  --coverage            Generate coverage report"
    echo "  --setup               Setup test environment"
    echo "  --teardown            Teardown test environment"
    echo "  --help                Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0                          # Run all tests (auto-detect environment)"
    echo "  $0 --env=wp-env --suite=unit    # Run unit tests in wp-env"
    echo "  $0 --env=manual --coverage       # Run all tests with coverage (manual setup)"
    echo "  $0 --setup --env=wp-env          # Setup wp-env environment"
}

# Default values
ENVIRONMENT="auto"
TEST_SUITE="all"
COVERAGE=false
SETUP=false
TEARDOWN=false

# Parse command line arguments
for arg in "$@"; do
    case $arg in
        --env=*)
            ENVIRONMENT="${arg#*=}"
            shift
            ;;
        --suite=*)
            TEST_SUITE="${arg#*=}"
            shift
            ;;
        --coverage)
            COVERAGE=true
            shift
            ;;
        --setup)
            SETUP=true
            shift
            ;;
        --teardown)
            TEARDOWN=true
            shift
            ;;
        --help)
            usage
            exit 0
            ;;
        *)
            echo "Unknown option: $arg"
            usage
            exit 1
            ;;
    esac
done

# Function to detect environment
detect_environment() {
    if [ -f ".wp-env.json" ] && command -v wp-env &> /dev/null; then
        echo "wp-env"
    elif [ ! -z "$WP_TESTS_DIR" ]; then
        echo "manual"
    elif command -v docker &> /dev/null && docker ps &> /dev/null; then
        echo "docker"
    else
        echo "unknown"
    fi
}

# Function to setup wp-env environment
setup_wp_env() {
    print_color $BLUE "Setting up wp-env environment..."

    if ! command -v wp-env &> /dev/null; then
        print_color $RED "wp-env not found. Installing @wordpress/env..."
        npm install -g @wordpress/env
    fi

    # Start wp-env
    wp-env start

    # Install test database
    wp-env run tests-cli wp core install \
        --url="http://localhost:8889" \
        --title="SkillPulse LMS Tests" \
        --admin_user="admin" \
        --admin_password="password" \
        --admin_email="admin@example.org" \
        --skip-email

    print_color $GREEN "wp-env environment setup complete!"
}

# Function to setup manual environment
setup_manual() {
    print_color $BLUE "Setting up manual test environment..."

    if [ ! -f "bin/install-wp-tests.sh" ]; then
        print_color $RED "install-wp-tests.sh not found!"
        exit 1
    fi

    # Run the install script
    bash bin/install-wp-tests.sh wordpress_test root '' localhost latest

    print_color $GREEN "Manual test environment setup complete!"
}

# Function to run tests in wp-env
run_wp_env_tests() {
    print_color $BLUE "Running tests in wp-env environment..."

    # Ensure wp-env is running
    if ! wp-env status &> /dev/null; then
        print_color $YELLOW "Starting wp-env..."
        wp-env start
    fi

    case $TEST_SUITE in
        "unit")
            wp-env run tests-cli --env-cwd=wp-content/plugins/skillpulse-lms vendor/bin/phpunit --testsuite=unit
            ;;
        "integration")
            wp-env run tests-cli --env-cwd=wp-content/plugins/skillpulse-lms vendor/bin/phpunit --testsuite=integration
            ;;
        "all")
            wp-env run tests-cli --env-cwd=wp-content/plugins/skillpulse-lms vendor/bin/phpunit
            ;;
    esac
}

# Function to run tests manually
run_manual_tests() {
    print_color $BLUE "Running tests in manual environment..."

    if [ "$COVERAGE" = true ]; then
        case $TEST_SUITE in
            "unit")
                composer test:unit -- --coverage-html ./coverage/html
                ;;
            "integration")
                composer test:integration -- --coverage-html ./coverage/html
                ;;
            "all")
                composer test -- --coverage-html ./coverage/html
                ;;
        esac
    else
        case $TEST_SUITE in
            "unit")
                composer test:unit
                ;;
            "integration")
                composer test:integration
                ;;
            "all")
                composer test
                ;;
        esac
    fi
}

# Function to run tests in docker
run_docker_tests() {
    print_color $BLUE "Running tests in Docker environment..."

    # This assumes you have a docker-compose setup for testing
    if [ -f "docker-compose.test.yml" ]; then
        docker-compose -f docker-compose.test.yml up --build --abort-on-container-exit
    else
        print_color $YELLOW "Docker testing not configured. Using manual method..."
        run_manual_tests
    fi
}

# Function to teardown environments
teardown_environment() {
    case $1 in
        "wp-env")
            print_color $BLUE "Tearing down wp-env environment..."
            wp-env stop
            ;;
        "manual"|"docker")
            print_color $BLUE "Manual/Docker teardown complete (no action needed)"
            ;;
    esac
}

# Main execution
main() {
    print_color $YELLOW "SkillPulse LMS Test Runner"
    print_color $YELLOW "=========================="

    # Auto-detect environment if not specified
    if [ "$ENVIRONMENT" = "auto" ]; then
        ENVIRONMENT=$(detect_environment)
        print_color $BLUE "Auto-detected environment: $ENVIRONMENT"
    fi

    # Validate environment
    case $ENVIRONMENT in
        "wp-env"|"manual"|"docker")
            ;;
        "unknown")
            print_color $RED "Could not detect test environment!"
            print_color $YELLOW "Please ensure one of the following:"
            print_color $YELLOW "  - wp-env is available and .wp-env.json exists"
            print_color $YELLOW "  - WP_TESTS_DIR environment variable is set"
            print_color $YELLOW "  - Docker is available"
            exit 1
            ;;
        *)
            print_color $RED "Invalid environment: $ENVIRONMENT"
            usage
            exit 1
            ;;
    esac

    # Handle setup
    if [ "$SETUP" = true ]; then
        case $ENVIRONMENT in
            "wp-env")
                setup_wp_env
                ;;
            "manual")
                setup_manual
                ;;
            "docker")
                print_color $YELLOW "Docker setup not implemented yet"
                ;;
        esac
        exit 0
    fi

    # Handle teardown
    if [ "$TEARDOWN" = true ]; then
        teardown_environment $ENVIRONMENT
        exit 0
    fi

    # Run tests
    print_color $BLUE "Running $TEST_SUITE tests in $ENVIRONMENT environment..."

    case $ENVIRONMENT in
        "wp-env")
            run_wp_env_tests
            ;;
        "manual")
            run_manual_tests
            ;;
        "docker")
            run_docker_tests
            ;;
    esac

    print_color $GREEN "Tests completed successfully!"
}

# Run main function
main