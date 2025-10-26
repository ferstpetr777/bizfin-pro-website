import { visitcustomizationscreen } from '../utils/st-import';

describe( 'selected site customization', () => {
	beforeEach( async () => {
		await visitcustomizationscreen();
	} );

	it( 'wait for fully page load and click on button', async () => {
		// Wait for fully page load and click on 'skip and continue' button
		page.waitForNavigation( {
			timeout: 10000,
			waitUntil: 'domcontentloaded',
		} );
		await page.waitForTimeout( 6000 );
		await page.waitForSelector( '#astra-starter-templates-preview' );
		await expect( page ).toClick(
			'.customize-business-logo .step-controls button.ist-next-step'
		);

		// Color selection and Font selection
		await page.waitForSelector( '.colors-section' );
		await expect( page ).toClick(
			'.colors-section .st-others-style-pallete .ist-color-palette:nth-child(3)'
		);
		await page.waitForSelector( '.typography-section' );
		await expect( page ).toClick(
			'.typography-section .ist-other-fonts .stc-tooltip:nth-child(3)'
		);

		// Click 'Continue' button
		await page.waitForSelector( '.step-controls' );
		await expect( page ).toClick( '.step-controls .ist-button' );
	} );
} );
