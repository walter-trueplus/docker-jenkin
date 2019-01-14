import OrderHelper from '../OrderHelper';
import Config from "../../config/Config";
import CurrencyHelper from "../CurrencyHelper";
import {when} from "jest-when";

describe('Test formatPrice function', () => {
    let mocksBackup = {};
    /* Mock Up Data for Integration Test */
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
                    currency_symbol: "€",
                    is_default: 0
                },
                {
                    code: "USD",
                    currency_name: "Dollar",
                    currency_symbol: "$",
                    is_default: 1
                }
            ]
        }
    };
    /* Prepare Data for Test Case */
    let data = [
        {
            testCaseId: 'MOD-01',
            title: 'OrderHelper::formatPrice - Display product with current currency when product price is 0',
            input: {
                price: 0,
                order: {
                    base_currency_code: "USD",
                    order_currency_code: "EUR"
                }
            },
            expect: '€0.00'
        },
        {
            testCaseId: 'MOD-02',
            title: 'OrderHelper::formatPrice - ' +
                'Display product with current currency when product price is round down',
            input: {
                price: 5.332,
                order: {
                    base_currency_code: "USD",
                    order_currency_code: "EUR"
                }
            },
            expect: '€5.33'
        },
        {
            testCaseId: 'MOD-03',
            title: 'OrderHelper::formatPrice - ' +
                'Display product with current currency when product price is round up',
            input: {
                price: 6.336,
                order: {
                    base_currency_code: "USD",
                    order_currency_code: "EUR"
                }
            },
            expect: '€6.34'
        },
        {
            testCaseId: 'MOD-04',
            title: 'OrderHelper::formatPrice - ' +
                'Display product with current currency when product price is negative',
            input: {
                price: -5.332,
                order: {
                    base_currency_code: "USD",
                    order_currency_code: "EUR"
                }
            },
            expect: '-€5.33'
        }
    ];

    /* Begin test formatPrice function */
    for (let i = 0; i < data.length; i++) {
        it(`[${data[i].testCaseId}] ${data[i].title}`, () => {
            let result = OrderHelper.formatPrice(
                data[i].input.price,
                data[i].input.order,
            );
            expect(result).toEqual(data[i].expect);
        });
    }
    /* End test formatPrice function */
});

describe('Test formatBasePrice function', () => {
    let mocksBackup = {};
    /* Mock Up Data for Integration Test */
    beforeAll(() => {
        mocksBackup.config = Config.config;

        Config.config = mockData.config;
    });
    afterAll(() => {
        Config.config = mocksBackup.config;
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
                    currency_symbol: "€",
                    is_default: 0
                },
                {
                    code: "USD",
                    currency_name: "Dollar",
                    currency_symbol: "$",
                    is_default: 1
                }
            ]
        }
    };
    /* Prepare Data for Test Case */
    let data = [
        {
            testCaseId: 'MOD-05',
            title: 'OrderHelper::formatBasePrice - Display product with base currency when product price is 0',
            input: {
                price: 0,
                order: {
                    base_currency_code: "USD",
                    order_currency_code: "EUR"
                }
            },
            expect: '$0.00'
        },
        {
            testCaseId: 'MOD-06',
            title: 'OrderHelper::formatBasePrice - ' +
                'Display product with base currency when product price is round down',
            input: {
                price: 5.332,
                order: {
                    base_currency_code: "USD",
                    order_currency_code: "EUR"
                }
            },
            expect: '$5.33'
        },
        {
            testCaseId: 'MOD-07',
            title: 'OrderHelper::formatBasePrice - ' +
                'Display product with base currency when product price is round up',
            input: {
                price: 6.336,
                order: {
                    base_currency_code: "USD",
                    order_currency_code: "EUR"
                }
            },
            expect: '$6.34'
        },
        {
            testCaseId: 'MOD-08',
            title: 'OrderHelper::formatBasePrice - ' +
                'Display product with base currency when product price is negative',
            input: {
                price: -5.332,
                order: {
                    base_currency_code: "USD",
                    order_currency_code: "EUR"
                }
            },
            expect: '-$5.33'
        }
    ];

    /* Begin test formatBasePrice function */
    for (let i = 0; i < data.length; i++) {
        it(`[${data[i].testCaseId}] ${data[i].title}`, () => {
            let result = OrderHelper.formatBasePrice(
                data[i].input.price,
                data[i].input.order,
            );
            expect(result).toEqual(data[i].expect);
        });
    }
    /* End test formatBasePrice function */
});

describe('Test validateAndConvertCurrency function', () => {
    let mocksBackup = {};
    beforeAll(() => {
        mocksBackup.getCurrencyFormat = CurrencyHelper.getCurrencyFormat;
        CurrencyHelper.getCurrencyFormat = jest.fn();
    });
    afterAll(() => {
        CurrencyHelper.getCurrencyFormat = mocksBackup.getCurrencyFormat;
    });

    /* Prepare Data for Test Case */
    let data = [
        {
            testCaseId: 'TPM-12',
            title: 'OrderHelper::validateAndConvertCurrency',
            input: {
                price: '11,115.55',
                order: {
                    order_currency_code: "VND"
                }
            },
            mockData: {
                currencyFormat: {
                    currency_code: "VND",
                    decimal_symbol: ".",
                    group_length: 3,
                    group_symbol: ",",
                    integer_required: 1,
                    pattern: "₫%s",
                    precision: 0,
                    required_precision: 0
                }
            },
            expect: '11115.55'
        },
        {
            testCaseId: 'TPM-13',
            title: 'OrderHelper::validateAndConvertCurrency',
            input: {
                price: '11,115.5566',
                order: {
                    order_currency_code: "VND"
                }
            },
            mockData: {
                currencyFormat: {
                    currency_code: "VND",
                    decimal_symbol: ".",
                    group_length: 3,
                    group_symbol: ",",
                    integer_required: 1,
                    pattern: "₫%s",
                    precision: 0,
                    required_precision: 0
                }
            },
            expect: '11115.56'
        }
    ];

    for (let i = 0; i < data.length; i++) {
        it(`[${data[i].testCaseId}] ${data[i].title}`, () => {
            when(CurrencyHelper.getCurrencyFormat).calledWith(data[i].input.order.order_currency_code)
                .mockReturnValue(data[i].mockData.currencyFormat);
            let result = OrderHelper.validateAndConvertCurrency(data[i].input.price, data[i].input.order);
            expect(result).toEqual(data[i].expect);
        });
    }
});
