
interface Array<T> {
  findIndex(compareFn: (e: T, i?: number, a?: T[]) => boolean, t?: any): number,
}