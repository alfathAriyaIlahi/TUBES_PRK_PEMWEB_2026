// MESIN RENDER TEMPLATE - ORANG 2
export function render(template, data, root, partials = {}) {
  const tokens = /\{\{([#>]?)(\w+)\}\}/g;
  let result = "";
  let offset = 0;
  for (let match; (match = tokens.exec(template));) {
    const [, type, key] = match;
    result += template.slice(offset, match.index);
    offset = tokens.lastIndex;
    if (type === "#") {
      const closing = "{{/" + key + "}}";
      const end = template.indexOf(closing, offset);
      if (end < 0) throw new Error("Blok template belum ditutup.");
      const body = template.slice(offset, end);
      for (const item of data[key] ?? []) {
        result += render(body, item, root, partials);
      }
      offset = tokens.lastIndex = end + closing.length;
    } else if (type === ">") {
      result += partials[key](data);
    } else {
      result += key === "root" ? root : (data[key] ?? "");
    }
  }
  return result + template.slice(offset);
}
