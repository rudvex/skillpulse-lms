module.exports = {
	extends: [
		'eslint:recommended',
		'plugin:react/recommended',
		'plugin:react-hooks/recommended'
	],
	plugins: [
		'react',
		'react-hooks'
	],
	env: {
		browser: true,
		es6: true,
		node: true,
		jquery: true
	},
	globals: {
		wp: 'readonly',
		jQuery: 'readonly',
		$: 'readonly',
		splms: 'readonly',
		splms_admin: 'readonly',
		splms_frontend: 'readonly',
		splms_dashboard: 'readonly',
		ajaxurl: 'readonly',
		tinymce: 'readonly',
		SPLMSCore: 'readonly',
		SPLMSCore_Data: 'readonly'
	},
	parserOptions: {
		ecmaVersion: 2020,
		sourceType: 'module',
		ecmaFeatures: {
			jsx: true
		}
	},
	settings: {
		react: {
			version: 'detect'
		}
	},
	rules: {
		// Basic rules
		'no-console': 'warn',
		'no-unused-vars': 'warn',
		'no-undef': 'error',
		'semi': ['error', 'always'],
		'quotes': ['error', 'single'],
		'indent': ['error', 'tab'],
		'no-case-declarations': 'error',

		// React specific rules
		'react/jsx-uses-react': 'error',
		'react/jsx-uses-vars': 'error',
		'react/prop-types': 'warn',
		'react/react-in-jsx-scope': 'off', // React 17+ doesn't require React import

		// Hook rules
		'react-hooks/rules-of-hooks': 'error',
		'react-hooks/exhaustive-deps': 'warn'
	}
};