<?php

namespace Tests\Onboarding\RestrictQueryParam;

use App\Models\Auth\User;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Support\Str;

class BusinessTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testShouldNotCreateNewBusiness()
    {
        $authUserToken = $this->getTokenPayload('client');
        $newBusiness = array_merge($this->getBusinessPayload(), $this->hasShareholderAndDirectors(),['test' => '123']);

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->postJson(route('business.create', ['test' => '12234']), $newBusiness)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'status' => 'failed',
            'message' => [ 'test' => ['The test is not a valid key.']],
        ]);
    }

    public function testShouldNotCreateNewBusinessAddedUnknowField()
    {
        $authUserToken = $this->getTokenPayload('agent');
        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $newBusiness = array_merge($this->getBusinessPayload(), $this->hasShareholderAndDirectors(),['test' => '123']);

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->postJson(route('business.create'), $newBusiness)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'status' => 'failed',
            'message' => [ 'test' => ['The test is not a valid key.']],
        ]);
    }


    public function testShouldNotUpdateBusiness()
    {
        $authUserToken = $this->getTokenPayload('agent');
        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $newBusiness = array_merge($this->getBusinessPayload(), $this->hasShareholderAndDirectors());

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->putJson(route('business.update', '9cfb80c1-452a-41e4-8cae-c4909a344ab?test=34578'))
        ->assertJson([
            'code' => Response::HTTP_NOT_FOUND,
            'status' => 'failed',
            'message' => 'No results found!',
        ]);
    }

    private function getBusinessPayload()
    {
        return [
            "name" => fake()->name(),
            "tax_information" => [
                "tax_country" => "DEU",
                "registration_number" => fake()->swiftBicNumber(),
                "registration_type" => "TRADING",
                "tax_identification_number" => "31659837651"
            ],
            "vat_number" => "updated_vat",
            "foundation_date" => "2022-09-30",
            "telephone" => "11223366699",
            "email" => "info@finxp.com",
            "website" => "https://www.finxp.com",
            "additional_website" => "https://my.finxp.com",
            "registered_address" => [
                "line_1" => "Line 1",
                "line_2" => "Line 2",
                "city" => "Amsterdam",
                "postal_code" => "Amsterdam",
                "country" => "DEU"
            ],
            "operational_address" => [
                "line_1" => "Line 1",
                "line_2" => "Line 2",
                "city" => "Amsterdam",
                "postal_code" => "Amsterdam",
                "country" => "MLT"
            ],
            "contact_details" => [
                "first_name" => "Jordan",
                "last_name" => "Ingles",
                "position_held" => "Company Executive Officer",
                "country_code" => "+49",
                "mobile_no" => "11223366699",
                "email" => "jordan.ingles@finxp.com"
            ],
            "business_details" => [
                "finxp_products" => [
                    "IBAN4U Payment Account",
                    "SEPA Direct Debit",
                    "Credit Card Processing"
                ],
                "industry_key" => [
                    "Plastering Contractors",
                    "Electrical Contractors"
                ],
                "number_employees" => 100,
                "share_capital" => 10150000.5,
                "number_shareholder" => 5,
                "number_directors" => 1,
                "previous_year_turnover" => "2021",
                "license_rep_juris" => "YES",
                "country_of_license" => "DEU",
                "country_juris_dealings" => ['MLT', 'DEU'],
                "business_year_count" => 2,
                "source_of_funds" => [
                    "TREASURY",
                    "DONATIONS"
                ],
                "terms_and_conditions" => true,
                "privacy_accepted" => true
            ],
            "iban4u_payment_account" => [
                "purpose_of_account_opening" => "reason goes here",
                "partners_incoming_transactions" => "foo bar baz",
                "country_origin" => ['FIN', 'AUS', 'DEU'],
                "country_remittance" => ['MLT', 'PHL'],
                "estimated_monthly_transactions" => 666,
                "average_amount_transaction_euro" => 1337,
                "accepting_third_party_funds" => "YES"
            ],
            "sepa_dd_direct_debit" => [
                "currently_processing_sepa_dd" => "YES",
                "sepa_dds" => [],
                "sepa_dd_volume_per_month" => 200
            ],
            "credit_card_processing" => [
                "currently_processing_cc_payments" => "YES",
                "offer_recurring_billing" => "YES",
                "offer_refunds" => "YES",
                "country" => "PHL",
                "distribution_sale_volume" => 99.99,
                "average_ticket_amount" => 66,
                "highest_ticket_amount" => 99,
                "alternative_payment_methods" => [
                    "GIRO",
                    "SOFORT"
                ],
                "method_currently_offered" => [
                    "GIRO",
                    "SOFORT"
                ],
                "cb_volumes_twelve_months" => 1234.56,
                "cc_volumes_twelve_months" => 1234.56,
                "refund_volumes_twelve_months" => 6699,
                "iban4u_processing" => "YES",
            ]
        ];
    }

    private function getBusinessNameAndProductPayload()
    {
        return [
            "name" => fake()->name(),
            "business_details" => [
                "finxp_products" => [
                    "IBAN4U Payment Account",
                    "SEPA Direct Debit"
                ]
            ],
            'corporate_saving' => true
        ];
    }

    private function hasShareholderAndDirectors()
    {
        return [
            "business_details" => [
                "finxp_products" => [
                    "IBAN4U Payment Account",
                    "SEPA Direct Debi",
                    "Credit Card Processing"
                ],
                "industry_key" => [
                    "Electrical Contractors",
                    "Siding - Contractors"
                ],
                "number_employees" => 100,
                "share_capital" => 10150000.5,
                "number_shareholder" => 5,
                "number_directors" => 1,
                "previous_year_turnover" => "2021",
                "license_rep_juris" => "YES",
                "country_of_license" => "DEU",
                "country_juris_dealings" => ['MLT', 'DEU'],
                "business_year_count" => 2,
                "source_of_funds" => [
                    "TREASURY",
                    "DONATIONS"
                ],
                "terms_and_conditions" => true,
                "privacy_accepted" => true
            ],
        ];
    }

    private function hasNoShareholderAndDirectors()
    {
        return [
            "business_details" => [
                "finxp_products" => [
                    "IBAN4U Payment Account",
                    "SEPA Direct Debi",
                    "Credit Card Processing"
                ],
                "industry_key" => [
                    "Tile Settings Contractors",
                    "Siding - Contractors"
                ],
                "number_employees" => 100,
                "share_capital" => 10150000.5,
                "number_shareholder" => 0,
                "number_directors" => 0,
                "previous_year_turnover" => "2021",
                "license_rep_juris" => "YES",
                "country_of_license" => "DEU",
                "country_juris_dealings" => ['MLT', 'DEU'],
                "business_year_count" => 2,
                "source_of_funds" => [
                    "TREASURY",
                    "DONATIONS"
                ],
                "terms_and_conditions" => true,
                "privacy_accepted" => true
            ],
        ];
    }

    private function hasNoNumberOfDirectors()
    {
        return [
            "business_details" => [
                "finxp_products" => [
                    "IBAN4U Payment Account",
                    "SEPA Direct Debit",
                    "Credit Card Processing"
                ],
                "industry_key" => [
                    "Miscellaneous Publishing and Printing",
                    "Tile Settings Contractors"
                ],
                "number_employees" => 100,
                "share_capital" => 10150000.5,
                "number_shareholder" => 1,
                "previous_year_turnover" => "2021",
                "license_rep_juris" => "YES",
                "country_of_license" => "DEU",
                "country_juris_dealings" => ['MLT', 'DEU'],
                "business_year_count" => 2,
                "source_of_funds" => [
                    "TREASURY",
                    "DONATIONS"
                ],
                "terms_and_conditions" => true,
                "privacy_accepted" => true
            ],
        ];
    }
}
