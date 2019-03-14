describe('The Home Page', function() {
    context('Home Page Actions', function() {
        it('successfully loads', function() {
            cy.visit('/') // change URL to match your dev URL
            cy.contains('OK, I agree').click()
            cy.contains('Resume').click()
            cy.get('div#resume').scrollIntoView().contains('Technical Skills').should('be.visible')
        })
    })
})