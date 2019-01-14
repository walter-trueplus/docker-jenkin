import React from 'react';
import CoreComponent from "../../../framework/component/CoreComponent";

export default class StylePrintComponent extends  CoreComponent {
    /**
     *  component render DOM expression
     *  @return string
     *
     * */
    template() {
        return (
            <style jsx="true">{`
                        .block-printreceipt {
                        margin: 0 auto;
                        padding: 25px 15px;
                        background-color: #fff;
                        text-align: center;
                        color: #1d1d1d;
                        font-size: 11px;
                        font-family: 'Helvetica';}


                        .block-printreceipt p {
                        margin-bottom: 3px;
                        margin-top: 0;
                    }

                        .block-printreceipt hr {
                        border-color: #cccdcd;
                        border-width: 1px 0 0 ;
                        border-style: dashed;
                        margin: 2px 0;
                    }

                        .block-printreceipt table {
                        width: calc(100% - 15px);
                        text-align: left;
                        line-height: 20px;
                    }

                        .block-printreceipt table tr td,
                        .block-printreceipt table tr th  {
                        vertical-align: text-top;
                        padding: 2px 0px;
                        border: none;
                        line-height: 16px;
                    }

                        .block-printreceipt table tr .t-qty,
                        .block-printreceipt table tr .t-price {
                    }

                        .block-printreceipt .t-name {
                        max-width: 120px;
                    }
                        .block-printreceip .t-refund-label {
                            max-width: 40px;
                        }

                        .block-printreceipt .t-qty,
                        .block-printreceipt .t-price,
                        .block-printreceipt .t-total {
                        white-space: nowrap;
                    }

                        .block-printreceipt .t-bundle {
                        padding-left: 10px;
                    }

                        .block-printreceipt i {
                        font-size: 10px;
                    }

                        .block-printreceipt .title {
                        font-size: 25px;
                        display: block;
                    }

                        .block-printreceipt .text-right {
                        text-align: right;
                    }

                        .block-printreceipt .text-center {
                        text-align: center;
                    }

                        .block-printreceipt .text-left {
                        text-align: left;
                    }

                    table {
                        font-size: 11px;
                    }


                    .block-printreceipt .reprint {
                        letter-spacing: 1.4px;
                        font-size: 12px;
                        font-weight: normal;
                        color: #9b9b9b;
                        line-height: 16px;
                        padding-top: 16px;
                    }

                    .block-printreceipt .reprint span {
                        display: inline-block;
                        vertical-align: middle;
                    }

                    .block-printreceipt .reprint strong {
                        letter-spacing: 0;
                        color: #4a4a4a;
                        padding: 0 3px;
                        display: inline-block;
                        vertical-align: middle;
                        font-weight: normal;
                    }

                    .hidden{display:none!important}

                    pre {
                        background-color: #fff;
                        border: none;
                        width: 250px;
                        margin: auto;
                    }

                    `}</style>
        );
    }
}

