import { __ } from '@wordpress/i18n';
import { TextControl, Button, BaseControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import './styles.scss';

const MatchingEditor = ({ value = [], onChange, label, help }) => {
	// Ensure value is an array
	const pairs = Array.isArray(value) ? value : [];
	
	// If empty, show at least one empty pair for user to start with
	const displayPairs = pairs.length > 0 ? pairs : [{ left: '', right: '' }];

	/**
	 * Add a new pair
	 */
	const addPair = () => {
		const newPairs = [...pairs, { left: '', right: '' }];
		onChange(newPairs);
	};

	/**
	 * Update a specific pair
	 */
	const updatePair = (index, field, newValue) => {
		// If pairs is empty but we're updating displayPairs, initialize pairs first
		const workingPairs = pairs.length > 0 ? [...pairs] : [{ left: '', right: '' }];
		workingPairs[index] = {
			...workingPairs[index],
			[field]: newValue
		};
		onChange(workingPairs);
	};

	/**
	 * Remove a pair
	 */
	const removePair = (index) => {
		const newPairs = pairs.filter((_, i) => i !== index);
		onChange(newPairs);
	};

	return (
		<BaseControl
			label={label || __('Matching Pairs', 'skillpulse-lms')}
			help={help || __('Add pairs of items that students need to match.', 'skillpulse-lms')}
			className="splms-matching-editor"
		>
			<div className="splms-matching-editor-pairs">
				{displayPairs.map((pair, index) => (
					<div key={index} className="splms-matching-editor-pair">
						<div className="splms-matching-editor-pair-fields">
							<TextControl
								label={__('Left Item', 'skillpulse-lms')}
								value={pair.left || ''}
								onChange={(newValue) => updatePair(index, 'left', newValue)}
								placeholder={__('Enter left item...', 'skillpulse-lms')}
								className="splms-matching-editor-left splms-field"
							/>
							<div className="splms-matching-editor-arrow">↔</div>
							<TextControl
								label={__('Right Item', 'skillpulse-lms')}
								value={pair.right || ''}
								onChange={(newValue) => updatePair(index, 'right', newValue)}
								placeholder={__('Enter right item...', 'skillpulse-lms')}
								className="splms-matching-editor-right splms-field"
							/>
						</div>
						<Button
							isDestructive
							isSmall
							onClick={() => removePair(index)}
							className="splms-matching-editor-remove"
							disabled={displayPairs.length <= 1}
						>
							{__('Remove', 'skillpulse-lms')}
						</Button>
					</div>
				))}
			</div>
			<Button
				variant="secondary"
				onClick={addPair}
				className="splms-matching-editor-add"
			>
				{__('Add Pair', 'skillpulse-lms')}
			</Button>
		</BaseControl>
	);
};

export default MatchingEditor;

