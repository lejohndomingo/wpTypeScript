/** Recursively marks all properties as optional */
type DeepPartial<T> = T extends object ? { [K in keyof T]?: DeepPartial<T[K]> } : T;

/** Recursively marks all properties as required (removes optionality) */
type DeepRequired<T> = T extends object ? { [K in keyof T]-?: DeepRequired<T[K]> } : T;

/** Recursively marks all properties as readonly */
type DeepReadonly<T> = T extends object ? { readonly [K in keyof T]: DeepReadonly<T[K]> } : T;

/** Union with `null` */
type Nullable<T> = T | null;

/** Union with `undefined` */
type Optional<T> = T | undefined;

/** Extracts the value type of an object type */
type ValueOf<T> = T[keyof T];

/** Picks keys whose values extend a given type */
type PickByValue<T, V> = Pick<T, { [K in keyof T]: T[K] extends V ? K : never }[keyof T]>;

/** Omits keys whose values extend a given type */
type OmitByValue<T, V> = Pick<T, { [K in keyof T]: T[K] extends V ? never : K }[keyof T]>;

/** Extracts the resolved return type of an async function */
type AsyncReturnType<T extends (...args: unknown[]) => unknown> = T extends (
  ...args: unknown[]
) => Promise<infer R>
  ? R
  : ReturnType<T>;

/** Requires at least one property from an object type to be present */
type AtLeastOne<T> = T extends object
  ? { [K in keyof T]-?: Pick<T, K> & Partial<Omit<T, K>> }[keyof T]
  : T;

/** Makes specific keys required */
type WithRequired<T, K extends keyof T> = Omit<T, K> & Required<Pick<T, K>>;

/** Makes specific keys optional */
type WithOptional<T, K extends keyof T> = Omit<T, K> & Partial<Pick<T, K>>;

/** Converts a union type to an intersection type */
type UnionToIntersection<U> = (U extends unknown ? (k: U) => void : never) extends (
  k: infer I,
) => void
  ? I
  : never;

/** Makes specific keys non-nullable */
type NonNullableFields<T, K extends keyof T = keyof T> = Omit<T, K> & {
  [P in K]-?: NonNullable<T[P]>;
};

/** Removes readonly modifier from all properties */
type Writable<T> = { -readonly [P in keyof T]: T[P] };

/** Nominal branding – creates a distinct type at the type level */
type Brand<T, B> = T & { __brand: B };

/** Prefixes all keys of an object type with a string literal */
type PrefixedKeys<T, P extends string> = {
  [K in keyof T as `${P}${string & K}`]: T[K];
};

/** Suffixes all keys of an object type with a string literal */
type SuffixedKeys<T, S extends string> = {
  [K in keyof T as `${string & K}${S}`]: T[K];
};

/** Blocks inference of a generic type parameter (use to prefer literal inference elsewhere) */
type NoInfer<T> = [T][T extends unknown ? 0 : never];

/** Merges two object types – `U` properties override `T` */
type Merge<T, U> = Omit<T, keyof U> & U;

/** A value or an array of that value */
type OneOrMany<T> = T | T[];

/** A value or a promise resolving to that value */
type MaybePromise<T> = T | Promise<T>;

/** Extracts keys whose values extend a given type */
type ExtractKeysByValue<T, V> = {
  [K in keyof T]: T[K] extends V ? K : never;
}[keyof T];

/** A partial record with a constrained key type */
type PartialRecord<K extends keyof unknown, V> = Partial<Record<K & string, V>>;

/** A fixed-length tuple type of length `N` */
type Tuple<T, N extends number> = N extends N
  ? number extends N
    ? T[]
    : _TupleOf<T, N, []>
  : never;

/** @internal recursive helper for Tuple */
type _TupleOf<T, N extends number, R extends unknown[]> = R['length'] extends N
  ? R
  : _TupleOf<T, N, [T, ...R]>;
