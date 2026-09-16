# Validation contract and source notes

Reviewed: September 16, 2026.

## Primary references

- [Cabinet Resolution No. 177, April 12, 2022](https://lex.uz/ru/docs/5955669): current PINFL structure, century mapping, calendar fields, repeating 7–3–1 weights and modulo-10 check digit. Its two worked examples are `31210932040247` and `40201902050010`.
- [Resolution No. 200, May 31, 1996](https://www.lex.uz/uz/acts/444922?ONDATE=21.11.2018+00): superseded historical reference, used only for additional published checksum fixtures `31210632040244` and `40201402050015`.
- [ITU Uzbekistan numbering plan](https://www.itu.int/dms_pub/itu-t/oth/02/02/T02020000E10002PDFE.pdf), communication dated February 10, 2023: country code, national length and allocated destination codes.
- [OQ operator number selector](https://www.oq.uz/en/numbers): the later `20` allocation supplements the ITU list.
- [UZTELECOM announcement, September 1, 2026](https://uztelecom.uz/en/news/promotions/beautiful-number-with-up-to-100-discount-uztelecom-extends-promotion-until-the-end-of-2026/): confirms mobile use of 70 alongside 77, 95 and 99. The package does not infer operator or service type from a prefix.
- [Cabinet Resolution No. 131, June 27, 2007](https://lex.uz/docs/-1225447): regional plate allocations covering 01–99 and ordinary registration layouts.

## Deliberate boundaries

1. Inputs are strings and digit matching is ASCII-only. Full-string anchors reject a trailing newline.
2. Phones require `+998` or `998`, a supported destination code and seven subscriber digits. No formatting normalization or subscriber lookup is performed.
3. PINFL requires the documented structure and checksum. Future dates are rejected; valid historical centuries are retained. Region/sequence assignments are not verified.
4. Passport/ID validation is a shared two-letter, seven-digit shape check. No authoritative complete series registry is claimed.
5. Plate validation supports ordinary individual and legal-entity layouts, fully compact or consistently spaced. It does not validate assignment or reserved series.
6. STIR is exactly nine digits. No unsupported checksum or entity-prefix restriction is invented. Even placeholder strings can pass a shape check; registry verification belongs to the host application.
7. Missing/empty inputs follow Laravel's non-implicit rule semantics; presence is controlled by `required`, null acceptance by `nullable`.

Phone allocations and document formats can evolve. Changes require a dated primary reference, positive/negative tests, documentation, and a release note.
