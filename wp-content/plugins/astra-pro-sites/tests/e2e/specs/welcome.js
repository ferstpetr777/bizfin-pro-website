import { visitST, clickToButton, scrollDown } from '../utils/st-import';

describe( 'welcome', () => {
	beforeEach( async () => {
		await visitST();
	} );

	it( 'on welcome video screen with click', async () => {
		const nodes = await page.$x(
			'//h1[contains(text(), "Getting Started with Starter Templates")]'
		);
		await expect( nodes ).not.toHaveLength( 0 );
		await clickToButton( '#st-welcome-video' );
		await page.waitForTimeout( 1000 );
		await clickToButton( '#st-welcome-video' );
		await expect( page ).toMatchElement( '.get-started-wrap button' );
		await scrollDown( page, '.get-started-wrap button' );
		await clickToButton( '.get-started-wrap button' );
	} );
} );
