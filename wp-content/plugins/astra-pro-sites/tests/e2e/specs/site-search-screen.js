import {
	visitsitesearch,
	selectSite,
	clickToButton,
	selectBuilder,
} from '../utils/st-import';

describe( 'site search screen', () => {
	beforeEach( async () => {
		await visitsitesearch();
	} );

	it( 'check content and select any editor and confirmed builder results', async () => {
		const nodes = await page.$x(
			'//h1[contains(text(), "What type of website are you building?")]'
		);
		await expect( nodes ).not.toHaveLength( 0 );

		// Click editor selector dropdown and select with compare sites
		await clickToButton( '.st-page-builder-filter' );
		await page.waitForSelector( '.stc-toggle-dropdown-popup' );
		await selectBuilder( 'Block Editor' );
		const blockeditorsites = [];
		const totalSelectedSites = await page.$$(
			'.stc-grid-item .stc-grid-item-title'
		);
		for ( let i = 0; i < 6; i++ ) {
			blockeditorsites.push(
				await (
					await totalSelectedSites[ i ].getProperty( 'innerText' )
				 ).jsonValue()
			);
		}

		await clickToButton( '.st-page-builder-filter' );
		await selectBuilder( 'Elementor' );
		const elementeditorsites = [];
		const totalSelectedAnotherSites = await page.$$(
			'.stc-grid-item .stc-grid-item-title'
		);
		for ( let i = 0; i < 6; i++ ) {
			elementeditorsites.push(
				await (
					await totalSelectedAnotherSites[ i ].getProperty(
						'innerText'
					)
				 ).jsonValue()
			);
		}
	} );

	it( 'favourite functionality test with comparison', async () => {
		// Favourite icon hover and click event
		await page.hover( '.st-my-favorite' );
		await page.waitForSelector( '.stc-tooltip-visible' );
		await clickToButton( '.st-my-favorite' );
		const noFavorite = await page.$x(
			'//h3[contains(text(), "No favorites added. Press the heart icon to add templates as favorites.")]'
		);
		await expect( noFavorite ).not.toHaveLength( 0 );
		await clickToButton( '.st-my-favorite' );
		await page.waitForSelector( '.st-templates-content' );
		await expect( page ).toClick(
			'.stc-grid-item:nth-child(3) .stc-grid-favorite '
		);
		await page.waitForSelector( '.stc-tooltip .active' );
		const selectedfavourite = await page.evaluate(
			() =>
				document.querySelector( '.stc-grid-item:nth-child(3) .active' )
					.innerText
		);
		await clickToButton( '.st-my-favorite' );
		await page.waitForSelector( '.stc-tooltip .active' );
		await expect( page ).toMatchElement(
			'.stc-grid-item .stc-grid-item-title',
			{
				text: selectedfavourite,
			}
		);
	} );

	it( 'site search with hotel keyword', async () => {
		// Count number of sites on screen
		const totalSites = await page.$$eval(
			'.stc-grid-item',
			( selectedsites ) => selectedsites.length
		);
		// Focus on search and type hotel
		await page.focus( '.stc-search-input' );
		await page.type( '.stc-search-input', 'hotel' );

		// Wait for selector stc-grid-item
		await page.waitForSelector( '.stc-grid-item' );

		// Count number of sites
		const hotelSites = await page.$$eval(
			'.stc-grid-item',
			( anotherselected ) => anotherselected.length
		);

		// Assert the counts not match.
		await expect( totalSites ).not.toBe( hotelSites );

		// Focus on search and clear
		await page.focus( '.stc-search-input' );
		const inputValue = await page.$eval(
			'.stc-search-input',
			( el ) => el.value
		);

		for ( let i = 0; i < inputValue.length; i++ ) {
			await page.keyboard.press( 'Backspace' );
		}

		// Wait for text "Outdoor Adventure" to be present on the screen.
		await page.waitForSelector( '.stc-grid-item' );
		await selectSite( 'Outdoor Adventure' );
	} );
} );
