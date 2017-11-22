import { SelfServiceBreakListPage } from './app.po';

describe('self-service-break-list App', () => {
  let page: SelfServiceBreakListPage;

  beforeEach(() => {
    page = new SelfServiceBreakListPage();
  });

  it('should display message saying app works', () => {
    page.navigateTo();
    expect(page.getParagraphText()).toEqual('app works!');
  });
});
