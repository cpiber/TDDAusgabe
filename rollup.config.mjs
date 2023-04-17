import commonjs from '@rollup/plugin-commonjs';
import nodeResolve from '@rollup/plugin-node-resolve';
import typescript from '@rollup/plugin-typescript';
import terser from '@rollup/plugin-terser';

const build = process.env.NODE_ENV !== 'development';
const plugins = [
  typescript({ sourceMap: !build }),
  nodeResolve({
    preferBuiltins: false,
    browser: true,
  }),
  commonjs(),
];
if (build) plugins.push(terser({ format: { comments: false } }));

export default [
  {
    input: 'src/files/client.ts',
    output: {
      dir: 'build/tmp',
      name: 'client',
      sourcemap: build ? false : 'inline',
      format: 'iife',
    },
    plugins,
  },
  {
    input: 'src/files/card.ts',
    output: {
      dir: 'build/tmp',
      name: 'card',
      sourcemap: build ? false : 'inline',
      format: 'iife',
    },
    plugins,
  }
];