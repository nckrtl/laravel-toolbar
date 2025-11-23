export const getNonce = () => {
  // Check script tags
  const scripts = document.getElementsByTagName('script')
  for (let script of scripts) {
    if (script.nonce) return script.nonce
    const nonceAttr = script.getAttribute('nonce')
    if (nonceAttr) return nonceAttr
  }

  // Check meta tag
  const metaNonce = document.querySelector('meta[name="csp-nonce"]')
  return metaNonce?.getAttribute('content') || null
}