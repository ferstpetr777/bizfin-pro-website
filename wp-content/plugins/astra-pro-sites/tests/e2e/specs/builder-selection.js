import {
	visitbuilderselection,
	enableBrizyBuilder,
	disableBrizyBuilder,
} from '../utils/st-import';

describe( 'builder selection', () => {
	it( 'disable Brizy Builder & check builder counts', async () => {
		await disableBrizyBuilder();
		await visitbuilderselection();

		await page.waitForSelector( '.screen-description' );

		const totalBuilderCount = await page.$$eval(
			'.page-builder-item',
			( sources ) => sources.length
		);
		expect( totalBuilderCount ).toBe( 3 );

		await expect( page ).toClick( '.page-builder-item h6', {
			text: 'Elementor',
		} );
	} );

	it( 'enable Brizy Builder & check builder counts', async () => {
		await enableBrizyBuilder();
		await visitbuilderselection();

		await page.waitForSelector( '.screen-description' );

		const totalBuilderCount = await page.$$eval(
			'.page-builder-item',
			( sources ) => sources.length
		);
		expect( totalBuilderCount ).toBe( 4 );

		await expect( page ).toClick( '.page-builder-item h6', {
			text: 'Elementor',
		} );
	} );
} );
