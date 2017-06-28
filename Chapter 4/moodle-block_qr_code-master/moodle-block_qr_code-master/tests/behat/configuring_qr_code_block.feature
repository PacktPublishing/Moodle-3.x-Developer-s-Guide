@block @block_qr_code @core_block
Feature: Adding and configuring QR Code blocks
  In order to have custom blocks on a page
  As admin
  I need to be able to create, configure and change QR code blocks

  @javascript
  Scenario: Configuring the HTML block with Javascript on
    Given I log in as "admin"
    And I am on site homepage
    When I click on "Turn editing on" "link" in the "Administration" "block"
    And I add the "QR Code" block
    And I configure the "(new QR Code block)" block
    And I set the field "Content" to "Static text without a header"
    And I press "Save changes"
    Then I should not see "(new QR Code block)"
    And I configure the "block_qr_code" block
    And I set the field "Block title" to "The QR Code block header"
    And I set the field "Content" to "Static text with a header"
    And I press "Save changes"
    And "block_qr_code" "block" should exist
    And "The QR Code block header" "block" should exist
    And I should see "Static text with a header" in the "The QR Code block header" "block"

  Scenario: Configuring the HTML block with Javascript off
    Given I log in as "admin"
    And I am on site homepage
    When I click on "Turn editing on" "link" in the "Administration" "block"
    And I add the "QR Code" block
    And I configure the "(new QR Code block)" block
    And I set the field "Content" to "Static text without a header"
    And I press "Save changes"
    Then I should not see "(new QR Code block)"
    And I configure the "block_qr_code" block
    And I set the field "Block title" to "The QR Code block header"
    And I set the field "Content" to "Static text with a header"
    And I press "Save changes"
    And "block_qr_code" "block" should exist
    And "The QR Code block header" "block" should exist
    And I should see "Static text with a header" in the "The QR Code block header" "block"
