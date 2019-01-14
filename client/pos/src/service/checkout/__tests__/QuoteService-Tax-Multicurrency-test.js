import QuoteService from '../QuoteService';
import Config from "../../../config/Config";

describe('Integration test apply custom price', () => {
  beforeAll(() => {
    // Mock config for test env
    Config.config = {
      guest_customer: {},
      settings: [
        {path: "tax/calculation/algorithm", value: "TOTAL_BASE_CALCULATION"},
        {path: "tax/calculation/based_on", value: "billing"},
        {path: "customer/create_account/default_group", value: "1"},
      ],
      tax_rules: [
        {
          "id": 1,
          "code": "Rule1",
          "priority": 0,
          "position": 0,
          "customer_tax_class_ids": [3],
          "product_tax_class_ids": [2],
          "tax_rate_ids": [3],
          "calculate_subtotal":false
        }
      ],
      tax_rates: [
        {
          "id": 3,
          "tax_country_id": "US",
          "tax_region_id": 33,
          "region_name": "MI",
          "tax_postcode": "*",
          "rate": 8.25,
          "code": "US-MI-*-Rate 1",
          "titles": []
        },
      ],
      customer_groups: [
        {id: 0, code: "NOT LOGGED IN", tax_class_id: 3},
        {id: 1, code: "General", tax_class_id: 3},
      ],
      currencies: [
        {
          code: "EUR",
          currency_name: "Euro",
          currency_rate: 1,
          currency_symbol: "€",
          is_default: 0
        },
        {
          code: "USD",
          currency_name: "US Dollar",
          currency_rate: 1,
          currency_symbol: "$",
          is_default: 1,
        },
      ],
      current_currency_code: "EUR",
    };
    Config.location_address = {
      city: "Calder",
      country: "United States",
      country_id: "US",
      postcode: "49628-7978",
      region: {
        region: "Michigan",
        region_code: "MI",
        region_id: 33,
      },
      region_id: 33,
      street: "6146 Honey Bluff Parkway",
    };
  });

  let data = [
    {
      testCaseId: 'TAX-01',
      title: 'Calculate Tax with currency rate 10, tax rate 19%, qty 1',
      currency_rate: "10",
      tax_rate: 19,
      product_price: 0.01,
      qty: 1,
      expect: {
        subtotal: 0.1,
        base_subtotal: 0.01,
        tax_amount: 0.02,
        base_tax_amount: 0,
        grand_total: 0.12,
        base_grand_total: 0.01,
      },
    },
    {
      testCaseId: 'TAX-02',
      title: 'Calculate Tax with currency rate 10, tax rate 19%, qty 10',
      currency_rate: "10",
      tax_rate: 19,
      product_price: 0.01,
      qty: 10,
      expect: {
        subtotal: 1,
        base_subtotal: 0.1,
        tax_amount: 0.19,
        base_tax_amount: 0.02,
        grand_total: 1.19,
        base_grand_total: 0.12,
      },
    },
    {
      testCaseId: 'TAX-03',
      title: 'Calculate Tax with currency rate 0.01, tax rate 19%, qty 1',
      currency_rate: "0.01",
      tax_rate: 19,
      product_price: 1,
      qty: 1,
      expect: {
        subtotal: 0.01,
        base_subtotal: 1,
        tax_amount: 0,
        base_tax_amount: 0.19,
        grand_total: 0.01,
        base_grand_total: 1.19,
      },
    },
  ];

  data.forEach(testCase => {
    it(`[${testCase.testCaseId}] ${testCase.title}`, () => {
      Config.config.currencies[0].currency_rate = testCase.currency_rate;
      Config.config.tax_rates[0].rate = testCase.tax_rate;

      let item = {
        product: {
          price: testCase.product_price,
          tax_class_id: 2,
        },
        qty: testCase.qty,
      };

      let quote = QuoteService.collectTotals({
        addresses: [],
        items: [item],
      });

      expect(quote).toMatchObject(testCase.expect);
    });
  });
});
