/**
 * External dependencies
 */
import {
	ScrollView,
	TouchableWithoutFeedback,
	View,
	Text,
	Platform,
} from 'react-native';
/**
 * WordPress dependencies
 */
import {
	SETTINGS_DEFAULTS,
	__experimentalUseGradient,
} from '@wordpress/block-editor';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
/**
 * Internal dependencies
 */
import styles from './style.scss';
import ColorIndicator from '../color-indicator';

function ColorPalette( {
	setBackgroundColor,
	backgroundColor,
	clientId,
	currentSegment,
	onCustomPress,
} ) {
	const isGradient = currentSegment === 'Gradient';
	const isIOS = Platform.OS === 'ios';

	const { setGradient, gradientValue } = __experimentalUseGradient(
		{},
		clientId
	);

	const [ activeColor, setActiveColor ] = useState(
		gradientValue || backgroundColor
	);

	const defaultColors = SETTINGS_DEFAULTS.colors;
	const defaultGradientColors = SETTINGS_DEFAULTS.gradients;

	function onColorPress( value ) {
		setActiveColor( value );
		if ( isGradient ) {
			setGradient( value );
			setBackgroundColor();
		} else {
			setBackgroundColor( value );
			setGradient();
		}
	}

	const GradientSwatch = () => {
		return (
			<>
				{ defaultGradientColors.map( ( color ) => {
					const isSelected = color.gradient === activeColor;
					return (
						<TouchableWithoutFeedback
							onPress={ () => onColorPress( color.gradient ) }
							key={ color.gradient }
						>
							<View>
								<ColorIndicator
									color={ color.gradient }
									gradient
									isSelected={ isSelected }
								/>
							</View>
						</TouchableWithoutFeedback>
					);
				} ) }
			</>
		);
	};

	const ColorSwatch = () => {
		return (
			<View style={ { flexDirection: 'row' } }>
				{ defaultColors.map( ( color ) => {
					const isSelected = color.color === activeColor;
					return (
						<TouchableWithoutFeedback
							onPress={ () => onColorPress( color.color ) }
							key={ color.color }
						>
							<View>
								<ColorIndicator
									color={ color.color }
									isSelected={ isSelected }
								/>
							</View>
						</TouchableWithoutFeedback>
					);
				} ) }
				<View style={ styles.verticalSeparator } />
				<TouchableWithoutFeedback onPress={ onCustomPress }>
					<Text style={ styles.customButton }>
						{ isIOS ? __( 'Custom' ) : __( 'CUSTOM' ) }
					</Text>
				</TouchableWithoutFeedback>
			</View>
		);
	};

	return (
		<ScrollView
			contentContainerStyle={ styles.container }
			horizontal
			showsHorizontalScrollIndicator={ false }
			keyboardShouldPersistTaps={ true }
		>
			{ isGradient ? <GradientSwatch /> : <ColorSwatch /> }
		</ScrollView>
	);
}

export default ColorPalette;
