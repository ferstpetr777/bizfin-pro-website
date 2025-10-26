import { visitimportscreenscreen, clickToButton } from '../utils/st-import';

describe( 'one Last Step Screen', () => {
	beforeEach( async () => {
		await visitimportscreenscreen();
	} );

	it( 'submit the import form details', async () => {
		await expect( page ).toMatchElement( '.survey-container' );
		await clickToButton( '.submit-survey-btn' );

		await expect( page ).toMatchElement(
			'.subscription-agreement-checkbox-label'
		);
		await page.focus( 'input[name="first_name"]' );
		await page.type( 'input[name="first_name"]', 'Sadanand' );
		await page.focus( 'input[name="email"]' );
		await page.type( 'input[name="email"]', 'sadanandl@bsf.io' );
		await page.focus( 'select[name="wp_user_type"]' );
		await page.select( 'select[name="wp_user_type"]', '3' );
		await page.focus( 'select[name="build_website_for"]' );
		await page.select( 'select[name="build_website_for"]', '1' );
		await clickToButton( '.subscription-agreement-checkbox-label' );
	} );
} );
