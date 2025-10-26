import { visitAdminPage } from '@wordpress/e2e-test-utils';
import { siteReset } from '../../config/bootstrap';

// Selection to click
export const clickToButton = async ( selector ) => {
	await expect( page ).toClick( selector );
};

// Visit startet-template page
export const visitST = async () => {
	await siteReset();
	await visitAdminPage( 'themes.php', 'page=starter-templates' );
};

// Visit builder screen
export const visitbuilderselection = async () => {
	await visitST();
	await expect( page ).toMatchElement( '.get-started-wrap button' );
	await clickToButton( '.get-started-wrap button' );
};

// Select pagebuilder
export const selectBuilder = async ( builder = 'Elementor' ) => {
	await expect( page ).toClick(
		'.stc-toggle-dropdown-popup .stc-toggle-dropdown-popup-item .stc-logo-text',
		{ text: builder }
	);
};

// visit site search page
export const visitsitesearch = async () => {
	await visitbuilderselection();
	await expect( page ).toClick( '.page-builder-item h6', {
		text: 'Elementor',
	} );
};

// Select site for import
export const selectSite = async ( selector ) => {
	// Select site from screen
	await expect( page ).toMatchElement(
		'.stc-grid-item .stc-grid-item-title',
		{ text: selector }
	);
	await Promise.all( [
		await expect( page ).toClick( 'div.stc-grid-item' ), // Clicking the link will indirectly cause a navigation
		,
		await page.waitForNavigation( 'networkidle2' ), // The promise resolves after navigation has finished after no more than 2 request left
	] );
	// The promise resolved
};

// visit customization screen
export const visitcustomizationscreen = async () => {
	await visitsitesearch();

	// Select site for import
	await page.waitForSelector( '.stc-grid-item' );
	await selectSite( 'Outdoor Adventure' );
};

// scroll down with seletor
export const scrollDown = async ( page, selector ) => {
	await page.$eval( selector, ( e ) => {
		e.scrollIntoView( { behavior: 'smooth', block: 'end', inline: 'end' } );
	} );
};

// visit import-form screen
export const visitimportscreenscreen = async () => {
	await visitcustomizationscreen();
	await page.waitForTimeout( 6000 );
	await clickToButton(
		'.customize-business-logo .step-controls button.ist-next-step'
	);
	await page.waitForTimeout( 1000 );
	await clickToButton( '.typography-section .ist-button' );
};
