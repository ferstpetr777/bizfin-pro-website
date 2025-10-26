import { visitimportscreenscreen, clickToButton } from '../utils/st-import';

describe( 'sT Import Site', () => {
	beforeEach( async () => {
		await visitimportscreenscreen();
	} );

	it( 'import process', async () => {
		// Continue and go to next step from Color Palette step.
		await expect( page ).toMatchElement( '.survey-container' );
		await clickToButton( '.submit-survey-btn' );
		await page.waitForSelector( '.survey-form .submit-survey-btn' );
		await page.click( '.survey-form .submit-survey-btn' );

		// Get the percent progress.
		await page.waitForSelector( '.ist-import-progress-info-precent' );
		let progress = await page.evaluate(
			() =>
				document.querySelector( '.ist-import-progress-info-precent' )
					.innerText
		);

		do {
			progress = await page.evaluate(
				() =>
					document.querySelector(
						'.ist-import-progress-info-precent'
					).innerText
			);
		} while ( progress !== '100%' );

		await expect( progress ).toMatch( '100%' );

		// Final screen check with video play/pause
		await page.waitForSelector( '#st-information-video' );
		const finalscreen = await page.$x(
			'//h1[contains(text(), "Congratulations!")]'
		);
		await expect( finalscreen ).not.toHaveLength( 0 );
		await clickToButton( '#st-information-video' );
		await page.waitForTimeout( 2000 );
		await clickToButton( '#st-information-video' );
	} );
} );
