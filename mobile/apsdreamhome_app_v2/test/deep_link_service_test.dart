import 'package:flutter_test/flutter_test.dart';

import 'package:apsdreamhome_app_v2/core/services/deep_link_service.dart';

void main() {
  group('DeepLinkService.parseRoute', () {
    test('maps property links to the property detail route', () {
      expect(DeepLinkService.parseRoute('apsdreamhome://property/123'),
          '/property-detail/123');
      expect(DeepLinkService.parseRoute('apsdreamhome://property-detail/123'),
          '/property-detail/123');
    });

    test('maps colony and colony-plots links', () {
      expect(DeepLinkService.parseRoute('apsdreamhome://colony/3'),
          '/colony-detail/3');
      expect(DeepLinkService.parseRoute('apsdreamhome://colony-detail/3'),
          '/colony-detail/3');
      expect(DeepLinkService.parseRoute('apsdreamhome://colony/3/plots'),
          '/colony-plots/3');
      expect(DeepLinkService.parseRoute('apsdreamhome://colony-plots/3'),
          '/colony-plots/3');
    });

    test('maps booking line to /booking/{plotId}', () {
      expect(DeepLinkService.parseRoute('apsdreamhome://booking/55'),
          '/booking/55');
    });

    test('maps static section links', () {
      expect(DeepLinkService.parseRoute('apsdreamhome://offers'), '/offers');
      expect(DeepLinkService.parseRoute('apsdreamhome://offers?promo=FESTIVE21'),
          '/offers');
      expect(DeepLinkService.parseRoute('apsdreamhome://my-bookings'),
          '/my-bookings');
      expect(DeepLinkService.parseRoute('apsdreamhome://home'), '/home');
    });

    test('extracts digits from embedded ids', () {
      expect(DeepLinkService.parseRoute('apsdreamhome://property/plot-123'),
          '/property-detail/123');
    });

    test('rejects wrong scheme', () {
      expect(DeepLinkService.parseRoute('http://apsdreamhome.example/property/1'),
          isNull);
      expect(DeepLinkService.parseRoute('https://apsdreamhome.example/colony/1'),
          isNull);
    });

    test('rejects unknown hosts', () {
      expect(DeepLinkService.parseRoute('apsdreamhome://admin/delete'), isNull);
      expect(
          DeepLinkService.parseRoute('apsdreamhome://wp-admin/wp-login.php'),
          isNull);
    });

    test('rejects missing id segments', () {
      expect(DeepLinkService.parseRoute('apsdreamhome://property'), isNull);
      expect(DeepLinkService.parseRoute('apsdreamhome://property/'), isNull);
      expect(DeepLinkService.parseRoute('apsdreamhome://colony'), isNull);
      expect(DeepLinkService.parseRoute('apsdreamhome://colony/'), isNull);
      expect(DeepLinkService.parseRoute('apsdreamhome://booking'), isNull);
    });

    test('rejects malformed urls', () {
      expect(DeepLinkService.parseRoute('not-a-url'), isNull);
      expect(DeepLinkService.parseRoute(''), isNull);
      expect(DeepLinkService.parseRoute('apsdreamhome://'), isNull);
    });
  });
}
