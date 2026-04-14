import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { Panel, PanelBody, PanelRow, Button, TextControl, Spinner, Notice } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

/**
 * SectionPricingSummary Component
 *
 * Displays a summary of all sections with their pricing information.
 * Provides bulk pricing tools to set the same price for all sections.
 */
class SectionPricingSummary extends Component {
	constructor(props) {
		super(props);

		this.state = {
			sections: [],
			isLoading: true,
			error: null,
			bulkPrice: '',
			isBulkSaving: false,
			bulkError: null,
			bulkSuccess: false,
		};

		this.loadSectionsPricing = this.loadSectionsPricing.bind(this);
		this.handleBulkPricing = this.handleBulkPricing.bind(this);
		this.handleBulkPriceChange = this.handleBulkPriceChange.bind(this);
	}

	componentDidMount() {
		this.loadSectionsPricing();
	}

	/**
	 * Load sections with pricing from REST API.
	 */
	async loadSectionsPricing() {
		const { courseId } = this.props;

		this.setState({ isLoading: true, error: null });

		try {
			const response = await apiFetch({
				path: `/splms/v1/courses/${courseId}/sections-with-pricing`,
			});

			if (response.success) {
				this.setState({
					sections: response.data.sections || [],
					isLoading: false,
				});
			} else {
				throw new Error(response.message || __('Failed to load sections pricing.', 'skillpulse-lms'));
			}
		} catch (err) {
			console.error('Error loading sections pricing:', err);
			this.setState({
				error: err.message || __('Failed to load sections pricing.', 'skillpulse-lms'),
				isLoading: false,
			});
		}
	}

	/**
	 * Handle bulk pricing update.
	 */
	async handleBulkPricing() {
		const { courseId } = this.props;
		const { bulkPrice } = this.state;

		if (!bulkPrice || parseFloat(bulkPrice) < 0) {
			this.setState({
				bulkError: __('Please enter a valid price.', 'skillpulse-lms'),
			});
			return;
		}

		this.setState({
			isBulkSaving: true,
			bulkError: null,
			bulkSuccess: false,
		});

		try {
			const response = await apiFetch({
				path: `/splms/v1/courses/${courseId}/bulk-section-pricing`,
				method: 'POST',
				data: {
					price: parseFloat(bulkPrice),
					is_free: false,
				},
			});

			if (response.success) {
				if (window.skillpulseToast) {
					window.skillpulseToast.success(
						__('Bulk pricing applied successfully!', 'skillpulse-lms')
					);
				}

				this.setState({
					isBulkSaving: false,
					bulkSuccess: true,
					bulkPrice: '',
				});

				// Reload sections to show updated pricing.
				this.loadSectionsPricing();
			} else {
				throw new Error(response.message || __('Failed to apply bulk pricing.', 'skillpulse-lms'));
			}
		} catch (err) {
			console.error('Error applying bulk pricing:', err);
			this.setState({
				bulkError: err.message || __('Failed to apply bulk pricing.', 'skillpulse-lms'),
				isBulkSaving: false,
			});

			if (window.skillpulseToast) {
				window.skillpulseToast.error(err.message);
			}
		}
	}

	/**
	 * Handle bulk price input change.
	 */
	handleBulkPriceChange(value) {
		this.setState({ bulkPrice: value, bulkError: null, bulkSuccess: false });
	}

	/**
	 * Calculate pricing statistics.
	 */
	getPricingStats() {
		const { sections } = this.state;

		const stats = {
			totalSections: sections.length,
			paidSections: 0,
			freeSections: 0,
			totalPrice: 0,
		};

		sections.forEach((section) => {
			if (section.pricing && section.pricing.is_free) {
				stats.freeSections += 1;
			} else if (section.pricing && section.pricing.price > 0) {
				stats.paidSections += 1;
				stats.totalPrice += parseFloat(section.pricing.price);
			}
		});

		return stats;
	}

	/**
	 * Render pricing statistics cards.
	 */
	renderStats() {
		const stats = this.getPricingStats();

		return (
			<div className="section-pricing-stats" style={{
				display: 'grid',
				gridTemplateColumns: 'repeat(4, 1fr)',
				gap: '15px',
				marginBottom: '20px',
			}}>
				<div className="stat-card" style={{
					padding: '15px',
					background: '#f0f0f1',
					borderRadius: '4px',
					textAlign: 'center',
				}}>
					<div style={{ fontSize: '24px', fontWeight: 'bold', color: '#2271b1' }}>
						{stats.totalSections}
					</div>
					<div style={{ fontSize: '12px', color: '#646970' }}>
						{__('Total Sections', 'skillpulse-lms')}
					</div>
				</div>

				<div className="stat-card" style={{
					padding: '15px',
					background: '#f0f0f1',
					borderRadius: '4px',
					textAlign: 'center',
				}}>
					<div style={{ fontSize: '24px', fontWeight: 'bold', color: '#00a32a' }}>
						{stats.paidSections}
					</div>
					<div style={{ fontSize: '12px', color: '#646970' }}>
						{__('Paid Sections', 'skillpulse-lms')}
					</div>
				</div>

				<div className="stat-card" style={{
					padding: '15px',
					background: '#f0f0f1',
					borderRadius: '4px',
					textAlign: 'center',
				}}>
					<div style={{ fontSize: '24px', fontWeight: 'bold', color: '#dba617' }}>
						{stats.freeSections}
					</div>
					<div style={{ fontSize: '12px', color: '#646970' }}>
						{__('Free Sections', 'skillpulse-lms')}
					</div>
				</div>

				<div className="stat-card" style={{
					padding: '15px',
					background: '#f0f0f1',
					borderRadius: '4px',
					textAlign: 'center',
				}}>
					<div style={{ fontSize: '24px', fontWeight: 'bold', color: '#2271b1' }}>
						${stats.totalPrice.toFixed(2)}
					</div>
					<div style={{ fontSize: '12px', color: '#646970' }}>
						{__('Total Price', 'skillpulse-lms')}
					</div>
				</div>
			</div>
		);
	}

	/**
	 * Render sections pricing table.
	 */
	renderSectionsTable() {
		const { sections } = this.state;

		if (0 === sections.length) {
			return (
				<p style={{ textAlign: 'center', color: '#646970', padding: '20px' }}>
					{__('No sections found. Add sections to your course curriculum first.', 'skillpulse-lms')}
				</p>
			);
		}

		return (
			<table className="wp-list-table widefat fixed striped" style={{ marginTop: '15px' }}>
				<thead>
					<tr>
						<th style={{ width: '50%' }}>{__('Section Title', 'skillpulse-lms')}</th>
						<th style={{ width: '15%' }}>{__('Price', 'skillpulse-lms')}</th>
						<th style={{ width: '15%' }}>{__('Sale Price', 'skillpulse-lms')}</th>
						<th style={{ width: '10%' }}>{__('Preview', 'skillpulse-lms')}</th>
						<th style={{ width: '10%' }}>{__('Status', 'skillpulse-lms')}</th>
					</tr>
				</thead>
				<tbody>
					{sections.map((section) => {
						const pricing = section.pricing || {};
						const isFree = pricing.is_free || false;
						const price = pricing.price || 0;
						const salePrice = pricing.sale_price || 0;
						const previewEnabled = pricing.preview_enabled || false;

						return (
							<tr key={section.id}>
								<td>
									<strong>{section.title}</strong>
								</td>
								<td>
									{isFree ? (
										<span style={{ color: '#00a32a' }}>{__('Free', 'skillpulse-lms')}</span>
									) : (
										<span>${parseFloat(price).toFixed(2)}</span>
									)}
								</td>
								<td>
									{!isFree && salePrice > 0 ? (
										<span style={{ color: '#d63638' }}>${parseFloat(salePrice).toFixed(2)}</span>
									) : (
										<span style={{ color: '#646970' }}>—</span>
									)}
								</td>
								<td>
									{previewEnabled ? (
										<span style={{ color: '#2271b1' }}>✓</span>
									) : (
										<span style={{ color: '#646970' }}>—</span>
									)}
								</td>
								<td>
									{isFree ? (
										<span className="badge" style={{
											display: 'inline-block',
											padding: '2px 8px',
											fontSize: '11px',
											borderRadius: '3px',
											background: '#00a32a',
											color: '#fff',
										}}>
											{__('Free', 'skillpulse-lms')}
										</span>
									) : price > 0 ? (
										<span className="badge" style={{
											display: 'inline-block',
											padding: '2px 8px',
											fontSize: '11px',
											borderRadius: '3px',
											background: '#2271b1',
											color: '#fff',
										}}>
											{__('Paid', 'skillpulse-lms')}
										</span>
									) : (
										<span className="badge" style={{
											display: 'inline-block',
											padding: '2px 8px',
											fontSize: '11px',
											borderRadius: '3px',
											background: '#dba617',
											color: '#fff',
										}}>
											{__('Not Set', 'skillpulse-lms')}
										</span>
									)}
								</td>
							</tr>
						);
					})}
				</tbody>
			</table>
		);
	}

	/**
	 * Render bulk pricing form.
	 */
	renderBulkPricing() {
		const { bulkPrice, isBulkSaving, bulkError, bulkSuccess } = this.state;

		return (
			<div className="bulk-pricing-section" style={{
				marginTop: '20px',
				padding: '15px',
				background: '#f9f9f9',
				border: '1px solid #ddd',
				borderRadius: '4px',
			}}>
				<h4 style={{ marginTop: 0 }}>
					{__('Bulk Pricing', 'skillpulse-lms')}
				</h4>
				<p style={{ color: '#646970', fontSize: '13px' }}>
					{__('Apply the same price to all sections at once.', 'skillpulse-lms')}
				</p>

				{bulkError && (
					<Notice status="error" isDismissible={false} style={{ marginBottom: '10px' }}>
						{bulkError}
					</Notice>
				)}

				{bulkSuccess && (
					<Notice status="success" isDismissible={false} style={{ marginBottom: '10px' }}>
						{__('Bulk pricing applied successfully!', 'skillpulse-lms')}
					</Notice>
				)}

				<div style={{ display: 'flex', gap: '10px', alignItems: 'flex-end' }}>
					<TextControl
						label={__('Price ($)', 'skillpulse-lms')}
						type="number"
						value={bulkPrice}
						onChange={this.handleBulkPriceChange}
						min="0"
						step="0.01"
						style={{ flex: 1 }}
						disabled={isBulkSaving}
					/>
					<Button
						isPrimary
						onClick={this.handleBulkPricing}
						isBusy={isBulkSaving}
						disabled={isBulkSaving || !bulkPrice}
						style={{ marginBottom: '8px' }}
					>
						{isBulkSaving ? __('Applying...', 'skillpulse-lms') : __('Apply to All Sections', 'skillpulse-lms')}
					</Button>
				</div>
			</div>
		);
	}

	render() {
		const { isLoading, error } = this.state;

		return (
			<Panel>
				<PanelBody
					title={__('Section Pricing Summary', 'skillpulse-lms')}
					initialOpen={true}
				>
					{isLoading && (
						<div style={{ textAlign: 'center', padding: '40px' }}>
							<Spinner />
							<p>{__('Loading section pricing...', 'skillpulse-lms')}</p>
						</div>
					)}

					{error && !isLoading && (
						<Notice status="error" isDismissible={false}>
							{error}
						</Notice>
					)}

					{!isLoading && !error && (
						<>
							{this.renderStats()}
							{this.renderSectionsTable()}
							{this.renderBulkPricing()}
						</>
					)}
				</PanelBody>
			</Panel>
		);
	}
}

export default SectionPricingSummary;
