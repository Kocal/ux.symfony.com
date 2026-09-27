// Keeps the Inspector's scrollIntoView() calls inside the iframe, so the site does not scroll.
export function withDemoScrolling(action, view = window) {
  if (view.parent === view) return action();
  const prototype = view.Element.prototype;
  const original = prototype.scrollIntoView;
  prototype.scrollIntoView = function (options) {
    const alignment = typeof options === "boolean" ? { block: options ? "start" : "end" } : options;
    return original.call(this, { ...alignment, container: "nearest", behavior: "instant" });
  };
  try {
    return action();
  } finally {
    prototype.scrollIntoView = original;
  }
}
