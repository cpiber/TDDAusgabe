
interface ObjectConstructor {
  values<T>(o: object): T[],
  assign(o: object, ...s: object[]): object,
}
