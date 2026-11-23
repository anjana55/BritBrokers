/*
 * Builds the site's CSS and JavaScript bundles. The output is committed, so the server needs no Node.
 *
 *   npm install          (once; installs esbuild and the library versions in package.json)
 *   npm run build        (= node bin/build-assets.mjs)
 *
 * Bundles (loaded by templates/header.php):
 *   css/site.min.css   every page        js/site.min.js    every page
 *   css/admin.min.css  admin screens     js/admin.min.js   admin screens (+ the property page's form validator)
 *
 * To change what is in a bundle, edit the lists below and rebuild.
 */
import { readFileSync, writeFileSync, copyFileSync, mkdirSync } from "node:fs";
import { dirname, join, relative, resolve, posix } from "node:path";
import { fileURLToPath } from "node:url";
import { transformSync } from "esbuild";

const ROOT = resolve(dirname(fileURLToPath(import.meta.url)), "..");
const NM = join(ROOT, "node_modules");

// 1. Library versions from npm, copied into the site so they are served (and committed) like the rest
const vendor = [
	["jquery/dist/jquery.min.js", "js/vendor/jquery-3.7.1.min.js"],
	["jquery-migrate/dist/jquery-migrate.min.js", "js/vendor/jquery-migrate.min.js"],
	["bootstrap/dist/js/bootstrap.min.js", "js/vendor/bootstrap-3.4.1.min.js"],
	["bootstrap/dist/css/bootstrap.css", "css/vendor/bootstrap-3.4.1.css"],
	["bootstrap/dist/css/bootstrap-theme.css", "css/vendor/bootstrap-theme-3.4.1.css"],
];
mkdirSync(join(ROOT, "js/vendor"), { recursive: true });
mkdirSync(join(ROOT, "css/vendor"), { recursive: true });
for (const [from, to] of vendor) {
	copyFileSync(join(NM, from), join(ROOT, to));
}

// 2. Bundle contents, in load order
const bundles = {
	"css/site.min.css": [
		"css/vendor/bootstrap-3.4.1.css",
		"css/vendor/bootstrap-theme-3.4.1.css",
		"css/flexslider.css",
		"css/style.css",
		"font-awesome/css/font-awesome.css",
		"css/bootstrapValidator.min.css",
		"css/fixes.css",
	],
	"css/admin.min.css": [
		"css/bootstrap-select.css",
		"css/dropzone.css",
		"css/jquery-ui.css",
		"css/control-panel.css",
	],
	"js/site.min.js": [
		"js/vendor/jquery-3.7.1.min.js",
		"js/vendor/jquery-migrate.min.js",
		"js/migrate-quiet.js",
		"js/easing.js",
		"js/jquery.lazyload.js",
		"js/jquery.ui.totop.js",
		"js/vendor/bootstrap-3.4.1.min.js",
		"js/custom.js",
	],
	"js/admin.min.js": [
		"js/jquery-ui.js",
		"js/bootstrapValidator.min.js",
		"js/bootstrap-select.js",
		"js/dropzone.js",
		"js/jquery.confirm.js",
		"js/control-panel.js",
	],
	// The property page's "send to a friend" form needs only the validator
	"js/validator.min.js": [
		"js/bootstrapValidator.min.js",
	],
};

/** Read a CSS file, inline its @imports and rewrite url(...) paths so they work from outDir. */
function cssFile(file, outDir, seen = new Set()) {
	const abs = join(ROOT, file);
	if (seen.has(abs)) return "";
	seen.add(abs);
	const dir = dirname(file);
	let css = readFileSync(abs, "utf8").replace(/^\uFEFF/, "");
	css = css.replace(/@import\s+url\(\s*["']?([^"')]+)["']?\s*\)\s*;?|@import\s+["']([^"']+)["']\s*;?/g, (m, a, b) => {
		const target = a || b;
		if (/^(https?:)?\/\//.test(target)) return m; // external (Google Fonts): leave as is
		return cssFile(posix.join(dir, target), outDir, seen);
	});
	return css.replace(/url\(\s*(["']?)([^"')]+)\1\s*\)/g, (m, q, url) => {
		if (/^(data:|https?:|\/\/|\/|#)/.test(url)) return m;
		const [path, suffix = ""] = url.split(/(?=[?#])/);
		const rebased = relative(join(ROOT, outDir), join(ROOT, dir, path)).split("\\").join("/");
		return `url(${q}${rebased}${suffix}${q})`;
	});
}

for (const [out, files] of Object.entries(bundles)) {
	let code;
	if (out.endsWith(".css")) {
		// @import of external CSS must come first in a stylesheet
		let css = files.map((f) => `/* ${f} */\n` + cssFile(f, dirname(out))).join("\n");
		const imports = [];
		css = css.replace(/@import\s+url\([^)]*\)\s*;?/g, (m) => { imports.push(m.endsWith(";") ? m : m + ";"); return ""; });
		code = imports.join("\n") + "\n" + transformSync(css, { loader: "css", minify: true }).code;
	} else {
		// Each file is wrapped in its own statement boundary so a missing semicolon cannot join two files
		const js = files.map((f) => `/* ${f} */\n` + readFileSync(join(ROOT, f), "utf8").replace(/^\uFEFF/, "") + "\n;").join("\n");
		code = transformSync(js, { loader: "js", minify: true, legalComments: "none", target: "es2015" }).code;
	}
	writeFileSync(join(ROOT, out), code);
	console.log(`${out.padEnd(22)} ${(code.length / 1024).toFixed(0).padStart(5)} KB  from ${files.length} files`);
}
