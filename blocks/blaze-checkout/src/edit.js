import { __ } from '@wordpress/i18n';
import { 
	useBlockProps,
	InspectorControls,
	RichText,
	ColorPalette,
	PanelColorSettings
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	RangeControl,
	SelectControl,
	TabPanel,
	__experimentalBoxControl as BoxControl,
	__experimentalUnitControl as UnitControl
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import CheckoutPreview from './components/CheckoutPreview';

export default function Edit({ attributes, setAttributes }) {
	const {
		mainHeading,
		recipientDetailsHeading,
		orderSummaryHeading,
		editButtonText,
		createAccountHeading,
		createAccountText,
		optionalText,
		subscriptionWarning,
		stepLabels,
		accordionSettings,
		typography,
		colors,
		spacing,
		layout
	} = attributes;

	const blockProps = useBlockProps({
		className: 'blaze-checkout-block-editor'
	});

	const updateTypography = (section, property, value) => {
		setAttributes({
			typography: {
				...typography,
				[section]: {
					...typography[section],
					[property]: value
				}
			}
		});
	};

	const updateColors = (property, value) => {
		setAttributes({
			colors: {
				...colors,
				[property]: value
			}
		});
	};

	const updateSpacing = (property, value) => {
		setAttributes({
			spacing: {
				...spacing,
				[property]: value
			}
		});
	};

	const updateLayout = (property, value) => {
		setAttributes({
			layout: {
				...layout,
				[property]: value
			}
		});
	};

	const updateAccordionSettings = (device, property, value) => {
		setAttributes({
			accordionSettings: {
				...accordionSettings,
				[device]: {
					...accordionSettings[device],
					[property]: value
				}
			}
		});
	};

	const updateStepLabels = (step, value) => {
		setAttributes({
			stepLabels: {
				...stepLabels,
				[step]: value
			}
		});
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Content Settings', 'blocksy-child')} initialOpen={true}>
					<TextControl
						label={__('Main Heading', 'blocksy-child')}
						value={mainHeading}
						onChange={(value) => setAttributes({ mainHeading: value })}
					/>

					<h4 style={{ marginTop: '20px', marginBottom: '10px', fontSize: '14px', fontWeight: '600' }}>
						{__('Step Labels', 'blocksy-child')}
					</h4>
					<TextControl
						label={__('Step 1 Label', 'blocksy-child')}
						value={stepLabels?.step1 || ''}
						onChange={(value) => updateStepLabels('step1', value)}
						help={__('Label for the contact information step', 'blocksy-child')}
					/>
					<TextControl
						label={__('Step 2 Label', 'blocksy-child')}
						value={stepLabels?.step2 || ''}
						onChange={(value) => updateStepLabels('step2', value)}
						help={__('Label for the billing/shipping details step', 'blocksy-child')}
					/>
					<TextControl
						label={__('Step 3 Label', 'blocksy-child')}
						value={stepLabels?.step3 || ''}
						onChange={(value) => updateStepLabels('step3', value)}
						help={__('Label for the payment step', 'blocksy-child')}
					/>

					<h4 style={{ marginTop: '20px', marginBottom: '10px', fontSize: '14px', fontWeight: '600' }}>
						{__('Other Headings', 'blocksy-child')}
					</h4>
					<TextControl
						label={__('Recipients Details Heading', 'blocksy-child')}
						value={recipientDetailsHeading}
						onChange={(value) => setAttributes({ recipientDetailsHeading: value })}
					/>
					<TextControl
						label={__('Order Summary Heading', 'blocksy-child')}
						value={orderSummaryHeading}
						onChange={(value) => setAttributes({ orderSummaryHeading: value })}
					/>
					<TextControl
						label={__('Edit Button Text', 'blocksy-child')}
						value={editButtonText}
						onChange={(value) => setAttributes({ editButtonText: value })}
					/>
					<TextControl
						label={__('Create Account Heading', 'blocksy-child')}
						value={createAccountHeading}
						onChange={(value) => setAttributes({ createAccountHeading: value })}
					/>
					<TextControl
						label={__('Create Account Text', 'blocksy-child')}
						value={createAccountText}
						onChange={(value) => setAttributes({ createAccountText: value })}
					/>
					<TextControl
						label={__('Optional Text', 'blocksy-child')}
						value={optionalText}
						onChange={(value) => setAttributes({ optionalText: value })}
					/>
					<TextControl
						label={__('Subscription Warning', 'blocksy-child')}
						value={subscriptionWarning}
						onChange={(value) => setAttributes({ subscriptionWarning: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Accordion Settings', 'blocksy-child')} initialOpen={false}>
					<TabPanel
						className="accordion-settings-tabs"
						activeClass="active-tab"
						tabs={[
							{
								name: 'desktop',
								title: __('Desktop', 'blocksy-child'),
								className: 'tab-desktop',
							},
							{
								name: 'tablet',
								title: __('Tablet', 'blocksy-child'),
								className: 'tab-tablet',
							},
							{
								name: 'mobile',
								title: __('Mobile', 'blocksy-child'),
								className: 'tab-mobile',
							},
						]}
					>
						{(tab) => (
							<div>
								<ToggleControl
									label={__('Enable Accordion', 'blocksy-child')}
									checked={accordionSettings[tab.name].enabled}
									onChange={(value) => updateAccordionSettings(tab.name, 'enabled', value)}
								/>
								{accordionSettings[tab.name].enabled && (
									<ToggleControl
										label={__('Default Open', 'blocksy-child')}
										checked={accordionSettings[tab.name].defaultOpen}
										onChange={(value) => updateAccordionSettings(tab.name, 'defaultOpen', value)}
									/>
								)}
							</div>
						)}
					</TabPanel>
				</PanelBody>

				<PanelBody title={__('Typography', 'blocksy-child')} initialOpen={false}>
					<TabPanel
						className="typography-tabs"
						activeClass="active-tab"
						tabs={[
							{
								name: 'mainHeading',
								title: __('Main Heading', 'blocksy-child'),
							},
							{
								name: 'sectionHeading',
								title: __('Section Heading', 'blocksy-child'),
							},
							{
								name: 'bodyText',
								title: __('Body Text', 'blocksy-child'),
							},
							{
								name: 'labels',
								title: __('Labels', 'blocksy-child'),
							},
						]}
					>
						{(tab) => (
							<div>
								<UnitControl
									label={__('Font Size', 'blocksy-child')}
									value={typography[tab.name].fontSize}
									onChange={(value) => updateTypography(tab.name, 'fontSize', value)}
								/>
								<SelectControl
									label={__('Font Weight', 'blocksy-child')}
									value={typography[tab.name].fontWeight}
									options={[
										{ label: '300', value: '300' },
										{ label: '400', value: '400' },
										{ label: '500', value: '500' },
										{ label: '600', value: '600' },
										{ label: '700', value: '700' },
										{ label: '800', value: '800' },
									]}
									onChange={(value) => updateTypography(tab.name, 'fontWeight', value)}
								/>
								<UnitControl
									label={__('Line Height', 'blocksy-child')}
									value={typography[tab.name].lineHeight}
									onChange={(value) => updateTypography(tab.name, 'lineHeight', value)}
								/>
								<div style={{ marginBottom: '16px' }}>
									<label>{__('Color', 'blocksy-child')}</label>
									<ColorPalette
										value={typography[tab.name].color}
										onChange={(value) => updateTypography(tab.name, 'color', value)}
									/>
								</div>
							</div>
						)}
					</TabPanel>
				</PanelBody>

				<PanelColorSettings
					title={__('Color Settings', 'blocksy-child')}
					initialOpen={false}
					colorSettings={[
						{
							value: colors.primary,
							onChange: (value) => updateColors('primary', value),
							label: __('Primary Color', 'blocksy-child'),
						},
						{
							value: colors.secondary,
							onChange: (value) => updateColors('secondary', value),
							label: __('Secondary Color', 'blocksy-child'),
						},
						{
							value: colors.accent,
							onChange: (value) => updateColors('accent', value),
							label: __('Accent Color', 'blocksy-child'),
						},
						{
							value: colors.error,
							onChange: (value) => updateColors('error', value),
							label: __('Error Color', 'blocksy-child'),
						},
						{
							value: colors.success,
							onChange: (value) => updateColors('success', value),
							label: __('Success Color', 'blocksy-child'),
						},
						{
							value: colors.border,
							onChange: (value) => updateColors('border', value),
							label: __('Border Color', 'blocksy-child'),
						},
						{
							value: colors.background,
							onChange: (value) => updateColors('background', value),
							label: __('Background Color', 'blocksy-child'),
						},
					]}
				/>

				<PanelBody title={__('Spacing', 'blocksy-child')} initialOpen={false}>
					<UnitControl
						label={__('Section Padding', 'blocksy-child')}
						value={spacing.sectionPadding}
						onChange={(value) => updateSpacing('sectionPadding', value)}
					/>
					<UnitControl
						label={__('Element Margin', 'blocksy-child')}
						value={spacing.elementMargin}
						onChange={(value) => updateSpacing('elementMargin', value)}
					/>
					<UnitControl
						label={__('Button Padding', 'blocksy-child')}
						value={spacing.buttonPadding}
						onChange={(value) => updateSpacing('buttonPadding', value)}
					/>
					<UnitControl
						label={__('Input Padding', 'blocksy-child')}
						value={spacing.inputPadding}
						onChange={(value) => updateSpacing('inputPadding', value)}
					/>
				</PanelBody>

				<PanelBody title={__('Layout', 'blocksy-child')} initialOpen={false}>
					<UnitControl
						label={__('Max Width', 'blocksy-child')}
						value={layout.maxWidth}
						onChange={(value) => updateLayout('maxWidth', value)}
					/>
					<UnitControl
						label={__('Column Gap', 'blocksy-child')}
						value={layout.columnGap}
						onChange={(value) => updateLayout('columnGap', value)}
					/>
					<SelectControl
						label={__('Alignment', 'blocksy-child')}
						value={layout.alignment}
						options={[
							{ label: __('Left', 'blocksy-child'), value: 'left' },
							{ label: __('Center', 'blocksy-child'), value: 'center' },
							{ label: __('Right', 'blocksy-child'), value: 'right' },
						]}
						onChange={(value) => updateLayout('alignment', value)}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<CheckoutPreview attributes={attributes} />
			</div>
		</>
	);
}
