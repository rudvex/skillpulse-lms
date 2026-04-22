/**
 * Contextual Upgrade Prompt Modal Component
 *
 * Reusable modal component for displaying upgrade prompts when users
 * need a Pro license to access features.
 *
 * @since [SPLMS_VERSION]
 */

import React from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import {
	Modal,
	Button,
} from '@wordpress/components';
import { SplmsIcon } from '../../../components/SplmsIcon';

/**
 * Contextual Upgrade Prompt Component.
 *
 * @param {Object} props - Component props.
 * @param {string} props.feature - Feature name (courses, enrollments, certificates, email, api).
 * @param {boolean} props.isOpen - Whether the modal is open.
 * @param {Function} props.onClose - Function to close the modal.
 * @param {Function} props.onUpgrade - Function to handle upgrade action.
 * @param {string} props.className - Additional CSS classes.
 * @return {JSX.Element|null} Upgrade prompt modal.
 */
const ContextualUpgradePrompt = ({
	feature,
	isOpen = false,
	onClose = () => {},
	onUpgrade = () => {},
	className = ''
}) => {
	const { isLicenseValid } = useSelect((select) => {
		try {
			const licenseStore = select('splms/license');
			return {
				isLicenseValid: licenseStore.isLicenseValid(),
			};
		} catch (err) {
			return { isLicenseValid: false };
		}
	}, []);

	// Don't render if license is valid or modal is closed.
	if (isLicenseValid || !isOpen) {
		return null;
	}

	/**
	 * Handle upgrade button click.
	 */
	const handleUpgradeClick = () => {
		const upgradeUrl = `https://skillpulselms.com/pricing/?utm_source=feature-limit&utm_medium=admin&utm_campaign=${feature}-limit`;
		window.open(upgradeUrl, '_blank', 'noopener,noreferrer');
		onUpgrade();
	};

	/**
	 * Handle "I Have a License" button click.
	 */
	const handleLicensePageClick = () => {
		window.location.href = SPLMSCore_Data.adminUrl + '/admin.php?page=splms-license';
	};

	return (
		<Modal
			title={__('Upgrade to Pro', 'skillpulse-lms')}
			onRequestClose={onClose}
			className={`splms-contextual-upgrade-prompt ${className}`.trim()}
			shouldCloseOnClickOutside={true}
			isDismissible={true}
			size="medium"
		>
			<div className="splms-upgrade-prompt-content">
				<div className="splms-upgrade-prompt__usage">
					<p>{__('This feature requires an active Pro license.', 'skillpulse-lms')}</p>
				</div>

				<div className="splms-upgrade-prompt__actions">
					<div className="splms-upgrade-prompt__primary-actions">
						<Button
							variant="primary"
							size="large"
							onClick={handleUpgradeClick}
							className="splms-upgrade-prompt__upgrade-btn"
						>
							<SplmsIcon mode="wp" name="external" size={16} />
							{__('Upgrade Now', 'skillpulse-lms')}
						</Button>
						<Button
							variant="secondary"
							size="large"
							onClick={handleLicensePageClick}
							className="splms-upgrade-prompt__license-btn"
						>
							{__('I Have a License', 'skillpulse-lms')}
						</Button>
					</div>
					<div className="splms-upgrade-prompt__secondary-actions">
						<Button
							variant="link"
							size="small"
							onClick={onClose}
							className="splms-upgrade-prompt__dismiss-btn"
						>
							{__('Close', 'skillpulse-lms')}
						</Button>
					</div>
				</div>
			</div>
		</Modal>
	);
};

export default ContextualUpgradePrompt;
