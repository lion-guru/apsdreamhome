describe('Sample Test', function () {
  it('should pass a basic truthy test', function () {
    expect(true).toBeTrue();
  });

  it('should handle simple ES6 features', function () {
    const nums = [1, 2, 3];
    const doubled = nums.map(n => n * 2);
    expect(doubled).toEqual([2, 4, 6]);
  });
});
