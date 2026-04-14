import { __ } from '@wordpress/i18n';
import { Modal, ToggleControl, TextControl, Button, Spinner } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const SectionPricingPanel = ({ sectionId, sectionTitle, onClose, onSave }) => {
	const [pricing, setPricing] = useState({
		is_free: false,
		price: 0,
		sale_price: 0,
		sale_start_date: '',
		sale_end_date: '',
		preview_enabled: false,
		preview_items: {
			lessons: [],
			quizzes: [],
			assessments: [],
		},
		requires_previous_section: false,
	});

	const [items, setItems] = useState([]);
	const [itemsByType, setItemsByType] = useState({ lessons: [], quizzes: [], assessments: [] });
	const [isLoading, setIsLoading] = useState(true);
	const [isSaving, setIsSaving] = useState(false);
	const [error, setError] = useState(null);

	// Load Pricing and Items on Mount.
	useEffect(() => {
		loadPricingAndItems();
	}, [sectionId]);

	const loadPricingAndItems = async () => {
		setIsLoading(true);
		setError(null);

		try {
			// Load pricing.
			const pricingResponse = await apiFetch({
				path: `/splms/v1/sections/${sectionId}/pricing`,
			});

			if (pricingResponse && pricingResponse.success && pricingResponse.data) {
				const data = pricingResponse.data;
				setPricing({
					is_free: data.is_free || false,
					price: data.price || 0,
					sale_price: data.sale_price || 0,
					sale_start_date: data.sale_start_date || '',
					sale_end_date: data.sale_end_date || '',
					preview_enabled: data.preview_enabled || false,
					preview_items: data.preview_items || {
						lessons: [],
						quizzes: [],
						assessments: [],
					},
					requires_previous_section: data.requires_previous_section || false,
				});
			}

			// Load section items (lessons, quizzes, assessments).
			const itemsResponse = await apiFetch({
				path: `/splms/v1/section/${sectionId}/items`,
			});

			if (itemsResponse) {
				setItems(itemsResponse);

				// Group items by type for easier rendering.
				const grouped = itemsResponse.reduce((acc, item) => {
					const type = item.type.replace('sp-', '') + 's'; // 'sp-lesson' -> 'lessons'.
					if (!acc[type]) {
						acc[type] = [];
					}
					acc[type].push(item);
					return acc;
				}, { lessons: [], quizzes: [], assessments: [] });

				setItemsByType(grouped);
			}
		} catch (err) {
			console.error('Error loading section data:', err);
			setError(err.message || __('Failed to load section data.', 'skillpulse-lms'));
		} finally {
			setIsLoading(false);
		}
	};

	// Save Pricing.
	const handleSave = async () => {
		setIsSaving(true);
		setError(null);

		try {
			const response = await apiFetch({
				path: `/splms/v1/sections/${sectionId}/pricing`,
				method: 'POST',
				data: {
					is_free: pricing.is_free,
					price: parseFloat(pricing.price) || 0,
					sale_price: parseFloat(pricing.sale_price) || 0,
					sale_start_date: pricing.sale_start_date,
					sale_end_date: pricing.sale_end_date,
					preview_enabled: pricing.preview_enabled,
					preview_items: pricing.preview_items,
					requires_previous_section: pricing.requires_previous_section,
				},
			});

			if (response.success) {
				if (window.skillpulseToast) {
					window.skillpulseToast.success(__('Section pricing saved!', 'skillpulse-lms'));
				}

				// Callback to parent to refresh.
				if (onSave) {
					onSave(response.data);
				}

				onClose();
			} else {
				throw new Error(response.message || __('Failed to save pricing.', 'skillpulse-lms'));
			}
		} catch (err) {
			console.error('Error saving pricing:', err);
			setError(err.message || __('Failed to save pricing.', 'skillpulse-lms'));

			if (window.skillpulseToast) {
				window.skillpulseToast.error(err.message);
			}
		} finally {
			setIsSaving(false);
		}
	};

	// Field Update Handlers.
	const updateField = (field, value) => {
		setPricing((prev) => ({ ...prev, [field]: value }));
	};

	const togglePreviewItem = (itemType, itemId, checked) => {
		const newPreviewItems = { ...pricing.preview_items };

		if (checked) {
			// Add item ID to the appropriate array.
			newPreviewItems[itemType] = [...newPreviewItems[itemType], itemId];
		} else {
			// Remove item ID from the appropriate array.
			newPreviewItems[itemType] = newPreviewItems[itemType].filter((id) => id !== itemId);
		}

		updateField('preview_items', newPreviewItems);
	};

	// Render Loading State.
	if (isLoading) {
		return (
			<Modal
				title={__('Loading...', 'skillpulse-lms')}
				onRequestClose={onClose}
				className="splms-section-pricing-modal"
			>
				<div style={{ textAlign: 'center', padding: '40px' }}>
					<Spinner />
					<p>{__('Loading section pricing...', 'skillpulse-lms')}</p>
				</div>
			</Modal>
		);
	}

	return (
		<Modal
			title={`${__('Configure Pricing', 'skillpulse-lms')}: ${sectionTitle}`}
			onRequestClose={onClose}
			className="splms-section-pricing-modal"
			style={{ maxWidth: '600px' }}
		>
			<div className="splms-modal-wrapper">
				<div className="pricing-panel-content">
					{error && (
						<div className="error-notice" style={{
							padding: '10px',
							background: '#f8d7da',
							border: '1px solid #f5c6cb',
							borderRadius: '4px',
							marginBottom: '15px',
							color: '#721c24'
						}}>
							{error}
						</div>
					)}

				{/* Free Section Toggle */}
				<ToggleControl
					label={__('Free Section', 'skillpulse-lms')}
					help={__('Make this section available for free.', 'skillpulse-lms')}
					checked={pricing.is_free}
					onChange={(value) => updateField('is_free', value)}
				/>

				{/* Pricing Fields */}
				{!pricing.is_free && (
					<>
						<TextControl
							label={__('Section Price ($)', 'skillpulse-lms')}
							type="number"
							value={pricing.price}
							onChange={(value) => updateField('price', value)}
							min="0"
							step="0.01"
						/>

						<div style={{ margin: '20px 0', borderTop: '1px solid #ddd' }}></div>

						<h4 style={{ marginBottom: '10px', fontSize: '14px' }}>{__('Sale Pricing (Optional)', 'skillpulse-lms')}</h4>

						<TextControl
							label={__('Sale Price ($)', 'skillpulse-lms')}
							type="number"
							value={pricing.sale_price}
							onChange={(value) => updateField('sale_price', value)}
							min="0"
							step="0.01"
							help={__('Leave 0 for no sale.', 'skillpulse-lms')}
						/>

						{pricing.sale_price > 0 && (
							<>
								<TextControl
									label={__('Sale Start Date', 'skillpulse-lms')}
									type="date"
									value={pricing.sale_start_date}
									onChange={(value) => updateField('sale_start_date', value)}
								/>

								<TextControl
									label={__('Sale End Date', 'skillpulse-lms')}
									type="date"
									value={pricing.sale_end_date}
									onChange={(value) => updateField('sale_end_date', value)}
								/>
							</>
						)}
					</>
				)}

				<div style={{ margin: '20px 0', borderTop: '1px solid #ddd' }}></div>

				{/* Preview Settings */}
				<h4 style={{ marginBottom: '10px', fontSize: '14px' }}>{__('Preview Settings', 'skillpulse-lms')}</h4>

				<ToggleControl
					label={__('Enable Preview', 'skillpulse-lms')}
					help={__('Allow non-enrolled users to preview some items (lessons, quizzes).', 'skillpulse-lms')}
					checked={pricing.preview_enabled}
					onChange={(value) => updateField('preview_enabled', value)}
				/>

				{pricing.preview_enabled && items.length > 0 && (
					<div className="preview-items-selector" style={{ marginTop: '15px' }}>
						<label style={{ fontWeight: 'bold', marginBottom: '10px', display: 'block' }}>
							{__('Select Preview Items', 'skillpulse-lms')}
						</label>

						{/* Lessons */}
						{itemsByType.lessons.length > 0 && (
							<div className="item-type-group" style={{ marginBottom: '15px' }}>
								<h5 style={{ margin: '10px 0 5px', color: '#2271b1' }}>
									📖 {__('Lessons', 'skillpulse-lms')}
								</h5>
								{itemsByType.lessons.map((item) => (
									<ToggleControl
										key={item.id}
										label={item.title}
										checked={pricing.preview_items.lessons.includes(item.id)}
										onChange={(checked) => togglePreviewItem('lessons', item.id, checked)}
									/>
								))}
							</div>
						)}

						{/* Quizzes */}
						{itemsByType.quizzes.length > 0 && (
							<div className="item-type-group" style={{ marginBottom: '15px' }}>
								<h5 style={{ margin: '10px 0 5px', color: '#2271b1' }}>
									✏️ {__('Quizzes', 'skillpulse-lms')}
								</h5>
								{itemsByType.quizzes.map((item) => (
									<ToggleControl
										key={item.id}
										label={item.title}
										checked={pricing.preview_items.quizzes.includes(item.id)}
										onChange={(checked) => togglePreviewItem('quizzes', item.id, checked)}
									/>
								))}
							</div>
						)}

						{/* Assessments */}
						{itemsByType.assessments.length > 0 && (
							<div className="item-type-group" style={{ marginBottom: '15px' }}>
								<h5 style={{ margin: '10px 0 5px', color: '#2271b1' }}>
									🎓 {__('Assessments', 'skillpulse-lms')}
								</h5>
								{itemsByType.assessments.map((item) => (
									<ToggleControl
										key={item.id}
										label={item.title}
										checked={pricing.preview_items.assessments.includes(item.id)}
										onChange={(checked) => togglePreviewItem('assessments', item.id, checked)}
									/>
								))}
							</div>
						)}
					</div>
				)}

				{pricing.preview_enabled && items.length === 0 && (
					<p style={{ color: '#666', fontStyle: 'italic' }}>
						{__('No items found in this section. Add lessons, quizzes, or assessments first.', 'skillpulse-lms')}
					</p>
				)}

				<div style={{ margin: '20px 0', borderTop: '1px solid #ddd' }}></div>

				{/* Sequential Purchase */}
				<ToggleControl
					label={__('Requires Previous Section', 'skillpulse-lms')}
					help={__('Students must purchase the previous section before this one.', 'skillpulse-lms')}
					checked={pricing.requires_previous_section}
					onChange={(value) => updateField('requires_previous_section', value)}
				/>
				</div>

				{/* Modal Footer with Action Buttons */}
				<div className="modal-footer" style={{
					padding: '16px 20px',
					borderTop: '1px solid #ddd',
					display: 'flex',
					gap: '10px',
					justifyContent: 'flex-end',
					backgroundColor: '#fff',
					marginTop: '0'
				}}>
					<Button
						variant="secondary"
						onClick={onClose}
						disabled={isSaving}
					>
						{__('Cancel', 'skillpulse-lms')}
					</Button>
					<Button
						variant="primary"
						onClick={handleSave}
						disabled={isSaving}
					>
						{isSaving ? (
							<>
								<Spinner />
								{__('Saving Pricing...', 'skillpulse-lms')}
							</>
						) : (
							__('Save Pricing', 'skillpulse-lms')
						)}
					</Button>
				</div>
			</div>
		</Modal>
	);
};

export default SectionPricingPanel;
