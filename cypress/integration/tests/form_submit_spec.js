describe('Form Submit Test', function() {
    it('With Correct Input', function() {
        cy.visit('/') // change URL to match your dev URL
        cy.contains('OK, I agree').click()
        cy.contains('Contact Me').click()
        cy.get('div#contact_form').scrollIntoView().contains('Contact Me !!').should('be.visible')
        cy.get('input#edit-full-name').type('Shiva')
        cy.get('input#edit-email-address').type('shimar007@gmail.com')
        cy.get('input#edit-mobile').type('1231231223')
        cy.get('textarea#edit-your-message').type('Test Message')
        cy.get('input#edit-submit').click()
        cy.contains('Thank you for contacting me. I will get back to you as soon as possible.')
    })

    it('With Incorrect Input - Invalid Email Address', function() {
        cy.visit('/') // change URL to match your dev URL
        cy.contains('OK, I agree').click()
        cy.contains('Contact Me').click()
        cy.get('div#contact_form').scrollIntoView().contains('Contact Me !!').should('be.visible')
        cy.get('input#edit-full-name').type('Shiva')
        cy.get('input#edit-email-address').type('shimar007')
        cy.get('input#edit-mobile').type('1231231223')
        cy.get('textarea#edit-your-message').type('Test Message')
        cy.get('input#edit-submit').click()
        cy.contains('The email address')
    })
})