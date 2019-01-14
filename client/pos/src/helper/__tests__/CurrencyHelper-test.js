import CurrencyHelper from '../CurrencyHelper';
import Config from "../../config/Config";

describe('Test convertAndFormat function', () => {
    let mocksBackup = {};
    beforeAll(() => {
        mocksBackup.config = Config.config;

        Config.config = mockData.config;
    });
    afterAll(() => {
        Config.config = mocksBackup.config;
        CurrencyHelper.reset();
    });
    let mockData = {
        config: {
            base_currency_code: "USD",
            current_currency_code: "EUR",
            price_formats: [
                {
                    currency_code: "EUR",
                    decimal_symbol: ".",
                    group_length: 3,
                    group_symbol: ",",
                    integer_required: 0,
                    pattern: "€%s",
                    precision: 2,
                    required_precision: 2
                }
            ],
            currencies: [
                {
                    code: "EUR",
                    currency_name: "Euro",
                    currency_rate: "0.0049",
                    currency_symbol: "€",
                    is_default: 0
                }
            ]
        }
    };
    let data = [
        {
            testCaseId: 'MPL-01',
            title: 'CurrencyHelper::convertAndFormat - Display product with multi currency when product price is 0',
            input: {
                price: 0,
                currency: '',
                precision: null
            },
            expect: '€0.00'
        },
        {
            testCaseId: 'MPL-02',
            title: 'CurrencyHelper::convertAndFormat - ' +
                'Display product with multi currency when product price is NOT equal 0 and currency is null',
            input: {
                price: 5.33,
                currency: '',
                precision: 2
            },
            expect: '€0.03'
        },
        {
            testCaseId: 'MPL-03',
            title: 'CurrencyHelper::convertAndFormat - ' +
                'Display product with multi currency when product price is NOT  equal 0 ' +
                'and currency is NOT null and not have precision',
            input: {
                price: 6.33,
                currency: 'EUR'
            },
            expect: '€0.03'
        }
    ];

    /* Begin test convertAndFormat function */
    for (let i = 0; i < data.length; i++) {
        it(`[${data[i].testCaseId}] ${data[i].title}`, () => {
            let result = CurrencyHelper.convertAndFormat(
                data[i].input.price,
                data[i].input.currency,
                data[i].input.precision
            );
            expect(result).toEqual(data[i].expect);
        });
    }
    /* End test convertAndFormat function */
});

describe('Test format function', () => {
    let mocksBackup = {};
    beforeAll(() => {
        mocksBackup.config = Config.config;

        Config.config = mockData.config;
    });
    afterAll(() => {
        Config.config = mocksBackup.config;
        CurrencyHelper.reset();
    });
    let mockData = {
        config: {
            base_currency_code: "USD",
            current_currency_code: "EUR",
            price_formats: [
                {
                    currency_code: "EUR",
                    decimal_symbol: ".",
                    group_length: 3,
                    group_symbol: ",",
                    integer_required: 0,
                    pattern: "€%s",
                    precision: 2,
                    required_precision: 2
                },
                {
                    currency_code: "USD",
                    decimal_symbol: ".",
                    group_length: 3,
                    group_symbol: ",",
                    integer_required: 0,
                    pattern: "$%s",
                    precision: 2,
                    required_precision: 2
                }
            ],
            currencies: [
                {
                    code: "EUR",
                    currency_name: "Euro",
                    currency_rate: "0.0049",
                    currency_symbol: "€",
                    is_default: 0
                },
                {
                    code: "USD",
                    currency_name: "US Dollar",
                    currency_rate: 1,
                    currency_symbol: "$",
                    is_default: 1
                }
            ]
        }
    };
    let data = [
        {
            testCaseId: 'SCM-01',
            title: 'Function CurrencyHelper::format - when all parameters is null',
            input: {
                price: '',
                currency: '',
                precision: null
            },
            expect: '€0.00'
        },
        {
            testCaseId: 'SCM-02',
            title: '"Function CurrencyHelper::format \n' +
                '- Price is negative \n' +
                '- Currency is string and it also does NOT match any currency\n' +
                '- Precision is default value"',
            input: {
                price: -5.33,
                currency: 'RUE'
            },
            expect: '-€5.33'
        },
        {
            testCaseId: 'SCM-03',
            title: '"Function CurrencyHelper::format \n' +
                '- Price is positive \n' +
                '- Currency is string and it also match one currency\n' +
                '- Precision is default value"',
            input: {
                price: 5.33,
                currency: 'EUR',
                precision: 2
            },
            expect: '€5.33'
        },
        {
            testCaseId: 'SCM-04',
            title: '"Function CurrencyHelper::format \n' +
                '- Price is positive \n' +
                '- Currency is an object currency\n' +
                '- Precision is default value"',
            input: {
                price: 5.33,
                currency: {
                    code: "EUR",
                    currency_name: "Euro",
                    currency_rate: "0.0049",
                    currency_symbol: "€",
                    is_default: 0
                },
                precision: 2
            },
            expect: '€5.33'
        }
    ];

    /* Begin test format function */
    for (let i = 0; i < data.length; i++) {
        it(`[${data[i].testCaseId}] ${data[i].title}`, () => {
            let result = CurrencyHelper.format(data[i].input.price, data[i].input.currency, data[i].input.precision);
            expect(result).toEqual(data[i].expect);
        });
    }
    /* End test format function */
});

describe('Test convert function', () => {
    let mocksBackup = {};
    beforeAll(() => {
        mocksBackup.config = Config.config;

        Config.config = mockData.config;
    });
    afterAll(() => {
        Config.config = mocksBackup.config;
        CurrencyHelper.reset();
    });
    let mockData = {
        config: {
            base_currency_code: "USD",
            current_currency_code: "EUR",
            price_formats: [
                {
                    currency_code: "EUR",
                    decimal_symbol: ".",
                    group_length: 3,
                    group_symbol: ",",
                    integer_required: 0,
                    pattern: "€%s",
                    precision: 2,
                    required_precision: 2
                },
                {
                    currency_code: "USD",
                    decimal_symbol: ".",
                    group_length: 3,
                    group_symbol: ",",
                    integer_required: 0,
                    pattern: "$%s",
                    precision: 2,
                    required_precision: 2
                }
            ],
            currencies: [
                {
                    code: "EUR",
                    currency_name: "Euro",
                    currency_rate: "0.0049",
                    currency_symbol: "€",
                    is_default: 0
                },
                {
                    code: "USD",
                    currency_name: "US Dollar",
                    currency_rate: 1,
                    currency_symbol: "$",
                    is_default: 1
                }
            ]
        }
    };
    let data = [
        {
            testCaseId: 'SCM-05',
            title: 'Function CurrencyHelper::convert - when all parameters is null',
            input: {
                price: '',
                currency: null
            },
            expect: 0.00
        },
        {
            testCaseId: 'SCM-06',
            title: 'Function CurrencyHelper::convert \n' +
                '- Price is negative \n' +
                '- Currency is string and it also does NOT match any currency',
            input: {
                price: -5.33,
                currency: 'RUE'
            },
            expect: -0.026117
        },
        {
            testCaseId: 'SCM-07',
            title: 'Function CurrencyHelper::convert \n' +
                '- Price is positive \n' +
                '- Currency is string and it also match one currency',
            input: {
                price: 5.33,
                currency: 'EUR'
            },
            expect: 0.026117
        },
        {
            testCaseId: 'SCM-08',
            title: 'Function CurrencyHelper::convert \n' +
                '- Price is positive \n' +
                '- Currency is an object currency',
            input: {
                price: 6.33,
                currency: {
                    code: "EUR",
                    currency_name: "Euro",
                    currency_rate: "0.0049",
                    currency_symbol: "€",
                    is_default: 0
                }
            },
            expect: 0.031017
        },
        {
            testCaseId: 'SCM-9',
            title: 'Function CurrencyHelper::convert \n' +
                '- Price is positive \n' +
                '- Currency is string and it also match with base currency',
            input: {
                price: 5.33,
                currency: "USD"
            },
            expect: 5.33
        },
        {
            testCaseId: 'SCM-10',
            title: 'Function CurrencyHelper::convert - when price is null and not have currency',
            input: {
                price: ''
            },
            expect: 0.00
        }
    ];

    /* Begin test convert function */
    for (let i = 0; i < data.length; i++) {
        it(`[${data[i].testCaseId}] ${data[i].title}`, () => {
            let result = CurrencyHelper.convert(data[i].input.price, data[i].input.currency);
            expect(result).toEqual(data[i].expect);
        });
    }
    /* End test convert function */
});