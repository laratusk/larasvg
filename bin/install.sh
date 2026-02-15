#!/usr/bin/env bash
set -euo pipefail

# ============================================================================
#  LaraSVG — Provider Installer
#  Detects OS, checks installed providers, and installs inkscape or resvg.
#
#  Usage:
#    ./install.sh                  # Show status of all providers
#    ./install.sh inkscape         # Install Inkscape
#    ./install.sh resvg            # Install Resvg
#    ./install.sh --check          # JSON status output (used by artisan command)
# ============================================================================

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
DIM='\033[2m'
NC='\033[0m'

# ---------------------------------------------------------------------------
#  OS Detection
# ---------------------------------------------------------------------------
detect_os() {
    local os=""
    local distro=""

    case "$(uname -s)" in
        Darwin)
            os="macos"
            distro="macos"
            ;;
        Linux)
            os="linux"
            if [ -f /etc/os-release ]; then
                . /etc/os-release
                case "$ID" in
                    ubuntu|debian|linuxmint|pop)  distro="debian" ;;
                    fedora|rhel|centos|rocky|alma) distro="fedora" ;;
                    arch|manjaro|endeavouros)      distro="arch" ;;
                    alpine)                        distro="alpine" ;;
                    opensuse*|sles)                distro="suse" ;;
                    *)                             distro="unknown" ;;
                esac
            else
                distro="unknown"
            fi
            ;;
        *)
            os="unknown"
            distro="unknown"
            ;;
    esac

    echo "$os:$distro"
}

# ---------------------------------------------------------------------------
#  Version Detection
# ---------------------------------------------------------------------------
get_inkscape_version() {
    if command -v inkscape &>/dev/null; then
        inkscape --version 2>/dev/null | head -1 || echo ""
    else
        echo ""
    fi
}

get_resvg_version() {
    if command -v resvg &>/dev/null; then
        resvg --version 2>/dev/null | head -1 || echo ""
    else
        echo ""
    fi
}

get_rsvg_convert_version() {
    if command -v rsvg-convert &>/dev/null; then
        rsvg-convert --version 2>/dev/null | head -1 || echo ""
    else
        echo ""
    fi
}

get_inkscape_path() {
    command -v inkscape 2>/dev/null || echo ""
}

get_resvg_path() {
    command -v resvg 2>/dev/null || echo ""
}

get_rsvg_convert_path() {
    command -v rsvg-convert 2>/dev/null || echo ""
}

# ---------------------------------------------------------------------------
#  --check: JSON output for artisan command
# ---------------------------------------------------------------------------
if [ "${1:-}" = "--check" ]; then
    inkscape_version=$(get_inkscape_version)
    resvg_version=$(get_resvg_version)
    rsvg_convert_version=$(get_rsvg_convert_version)
    inkscape_path=$(get_inkscape_path)
    resvg_path=$(get_resvg_path)
    rsvg_convert_path=$(get_rsvg_convert_path)
    os_info=$(detect_os)

    cat <<EOF
{
    "os": "${os_info%%:*}",
    "distro": "${os_info##*:}",
    "inkscape": {
        "installed": $([ -n "$inkscape_version" ] && echo "true" || echo "false"),
        "version": "$inkscape_version",
        "path": "$inkscape_path"
    },
    "resvg": {
        "installed": $([ -n "$resvg_version" ] && echo "true" || echo "false"),
        "version": "$resvg_version",
        "path": "$resvg_path"
    },
    "rsvg-convert": {
        "installed": $([ -n "$rsvg_convert_version" ] && echo "true" || echo "false"),
        "version": "$rsvg_convert_version",
        "path": "$rsvg_convert_path"
    }
}
EOF
    exit 0
fi

# ---------------------------------------------------------------------------
#  Status Display
# ---------------------------------------------------------------------------
print_header() {
    echo ""
    echo -e "${BOLD}${CYAN}  _                   ______     ______${NC}"
    echo -e "${BOLD}${CYAN} | |                 / ___\\ \\   / / ___|${NC}"
    echo -e "${BOLD}${CYAN} | |    __ _ _ __ __| \\___ \\\\ \\ / / |  _${NC}"
    echo -e "${BOLD}${CYAN} | |   / _\` | '__/ _\` |___) |\\ V /| |_| |${NC}"
    echo -e "${BOLD}${CYAN} | |__| (_| | | | (_| |____/  \\_/  \\____|${NC}"
    echo -e "${BOLD}${CYAN} |_____\\__,_|_|  \\__,_|${NC}"
    echo ""
    echo -e "${DIM}  Provider Installer${NC}"
    echo ""
}

print_status() {
    local os_info
    os_info=$(detect_os)
    local os="${os_info%%:*}"
    local distro="${os_info##*:}"

    echo -e "  ${BOLD}System${NC}"
    echo -e "  OS:     ${BLUE}${os}${NC} (${distro})"
    echo ""

    echo -e "  ${BOLD}Providers${NC}"

    local inkscape_version
    inkscape_version=$(get_inkscape_version)
    if [ -n "$inkscape_version" ]; then
        echo -e "  ${GREEN}●${NC} Inkscape  ${GREEN}installed${NC}  ${DIM}${inkscape_version}${NC}"
        echo -e "              ${DIM}$(get_inkscape_path)${NC}"
    else
        echo -e "  ${RED}○${NC} Inkscape  ${RED}not installed${NC}"
    fi

    local resvg_version
    resvg_version=$(get_resvg_version)
    if [ -n "$resvg_version" ]; then
        echo -e "  ${GREEN}●${NC} Resvg        ${GREEN}installed${NC}  ${DIM}${resvg_version}${NC}"
        echo -e "                 ${DIM}$(get_resvg_path)${NC}"
    else
        echo -e "  ${RED}○${NC} Resvg        ${RED}not installed${NC}"
    fi

    local rsvg_convert_version
    rsvg_convert_version=$(get_rsvg_convert_version)
    if [ -n "$rsvg_convert_version" ]; then
        echo -e "  ${GREEN}●${NC} Rsvg-convert ${GREEN}installed${NC}  ${DIM}${rsvg_convert_version}${NC}"
        echo -e "                 ${DIM}$(get_rsvg_convert_path)${NC}"
    else
        echo -e "  ${RED}○${NC} Rsvg-convert ${RED}not installed${NC}"
    fi

    echo ""
}

# ---------------------------------------------------------------------------
#  Install Functions
# ---------------------------------------------------------------------------
install_inkscape() {
    local os_info
    os_info=$(detect_os)
    local os="${os_info%%:*}"
    local distro="${os_info##*:}"

    echo -e "  ${BLUE}Installing Inkscape...${NC}"
    echo ""

    case "$distro" in
        macos)
            if command -v brew &>/dev/null; then
                echo -e "  ${DIM}Using Homebrew...${NC}"
                brew install --cask inkscape
            else
                echo -e "  ${RED}Homebrew not found.${NC}"
                echo -e "  Install Homebrew first: ${CYAN}https://brew.sh${NC}"
                echo -e "  Or download Inkscape from: ${CYAN}https://inkscape.org/release/${NC}"
                exit 1
            fi
            ;;
        debian)
            echo -e "  ${DIM}Using apt...${NC}"
            sudo apt-get update -qq
            sudo apt-get install -y inkscape
            ;;
        fedora)
            echo -e "  ${DIM}Using dnf...${NC}"
            sudo dnf install -y inkscape
            ;;
        arch)
            echo -e "  ${DIM}Using pacman...${NC}"
            sudo pacman -Syu --noconfirm inkscape
            ;;
        alpine)
            echo -e "  ${DIM}Using apk...${NC}"
            sudo apk add inkscape
            ;;
        suse)
            echo -e "  ${DIM}Using zypper...${NC}"
            sudo zypper install -y inkscape
            ;;
        *)
            echo -e "  ${RED}Unsupported OS for automatic installation.${NC}"
            echo -e "  Please install Inkscape manually: ${CYAN}https://inkscape.org/release/${NC}"
            exit 1
            ;;
    esac

    echo ""
    local version
    version=$(get_inkscape_version)
    if [ -n "$version" ]; then
        echo -e "  ${GREEN}Inkscape installed successfully!${NC}"
        echo -e "  ${DIM}${version}${NC}"
        echo -e "  ${DIM}$(get_inkscape_path)${NC}"
    else
        echo -e "  ${RED}Installation may have failed. Please check the output above.${NC}"
        exit 1
    fi
}

install_resvg() {
    local os_info
    os_info=$(detect_os)
    local os="${os_info%%:*}"
    local distro="${os_info##*:}"

    echo -e "  ${BLUE}Installing Resvg...${NC}"
    echo ""

    case "$distro" in
        macos)
            if command -v brew &>/dev/null; then
                echo -e "  ${DIM}Using Homebrew...${NC}"
                brew install resvg
            elif command -v cargo &>/dev/null; then
                echo -e "  ${DIM}Using Cargo...${NC}"
                cargo install resvg
            else
                echo -e "  ${RED}Neither Homebrew nor Cargo found.${NC}"
                echo -e "  Install via Homebrew: ${CYAN}brew install resvg${NC}"
                echo -e "  Or install Rust first: ${CYAN}https://rustup.rs${NC}"
                exit 1
            fi
            ;;
        debian|fedora|arch|alpine|suse)
            if command -v cargo &>/dev/null; then
                echo -e "  ${DIM}Using Cargo...${NC}"
                cargo install resvg
            else
                echo -e "  ${YELLOW}Cargo not found. Installing Rust toolchain first...${NC}"
                echo ""
                curl --proto '=https' --tlsv1.2 -sSf https://sh.rustup.rs | sh -s -- -y
                # shellcheck source=/dev/null
                source "$HOME/.cargo/env"
                echo ""
                echo -e "  ${DIM}Installing resvg via Cargo...${NC}"
                cargo install resvg
            fi
            ;;
        *)
            echo -e "  ${RED}Unsupported OS for automatic installation.${NC}"
            echo -e "  Install Rust (${CYAN}https://rustup.rs${NC}) then run: ${CYAN}cargo install resvg${NC}"
            exit 1
            ;;
    esac

    echo ""
    local version
    version=$(get_resvg_version)
    if [ -n "$version" ]; then
        echo -e "  ${GREEN}Resvg installed successfully!${NC}"
        echo -e "  ${DIM}${version}${NC}"
        echo -e "  ${DIM}$(get_resvg_path)${NC}"
    else
        echo -e "  ${RED}Installation may have failed. Please check the output above.${NC}"
        echo -e "  ${DIM}You may need to restart your terminal or add ~/.cargo/bin to your PATH.${NC}"
        exit 1
    fi
}

install_rsvg_convert() {
    local os_info
    os_info=$(detect_os)
    local distro="${os_info##*:}"

    echo -e "  ${BLUE}Installing rsvg-convert (librsvg)...${NC}"
    echo ""

    case "$distro" in
        macos)
            if command -v brew &>/dev/null; then
                echo -e "  ${DIM}Using Homebrew...${NC}"
                brew install librsvg
            else
                echo -e "  ${RED}Homebrew not found.${NC}"
                echo -e "  Install Homebrew first: ${CYAN}https://brew.sh${NC}"
                echo -e "  Then run: ${CYAN}brew install librsvg${NC}"
                exit 1
            fi
            ;;
        debian)
            echo -e "  ${DIM}Using apt...${NC}"
            sudo apt-get update -qq
            sudo apt-get install -y librsvg2-bin
            ;;
        fedora)
            echo -e "  ${DIM}Using dnf...${NC}"
            sudo dnf install -y librsvg2-tools
            ;;
        arch)
            echo -e "  ${DIM}Using pacman...${NC}"
            sudo pacman -Syu --noconfirm librsvg
            ;;
        alpine)
            echo -e "  ${DIM}Using apk...${NC}"
            sudo apk add rsvg-convert
            ;;
        suse)
            echo -e "  ${DIM}Using zypper...${NC}"
            sudo zypper install -y rsvg-convert
            ;;
        *)
            echo -e "  ${RED}Unsupported OS for automatic installation.${NC}"
            echo -e "  Please install librsvg manually:"
            echo -e "    Alpine:       ${CYAN}apk add rsvg-convert${NC}"
            echo -e "    Ubuntu/Debian:${CYAN}sudo apt-get install librsvg2-bin${NC}"
            echo -e "    Fedora/RHEL:  ${CYAN}sudo dnf install librsvg2-tools${NC}"
            echo -e "    Arch:         ${CYAN}sudo pacman -S librsvg${NC}"
            echo -e "    macOS:        ${CYAN}brew install librsvg${NC}"
            exit 1
            ;;
    esac

    echo ""
    local version
    version=$(get_rsvg_convert_version)
    if [ -n "$version" ]; then
        echo -e "  ${GREEN}rsvg-convert installed successfully!${NC}"
        echo -e "  ${DIM}${version}${NC}"
        echo -e "  ${DIM}$(get_rsvg_convert_path)${NC}"
    else
        echo -e "  ${RED}Installation may have failed. Please check the output above.${NC}"
        exit 1
    fi
}

# ---------------------------------------------------------------------------
#  Main
# ---------------------------------------------------------------------------
case "${1:-}" in
    inkscape)
        print_header
        existing=$(get_inkscape_version)
        if [ -n "$existing" ]; then
            echo -e "  ${GREEN}Inkscape is already installed.${NC}"
            echo -e "  ${DIM}${existing}${NC}"
            echo -e "  ${DIM}$(get_inkscape_path)${NC}"
            echo ""
            exit 0
        fi
        install_inkscape
        echo ""
        ;;
    resvg)
        print_header
        existing=$(get_resvg_version)
        if [ -n "$existing" ]; then
            echo -e "  ${GREEN}Resvg is already installed.${NC}"
            echo -e "  ${DIM}${existing}${NC}"
            echo -e "  ${DIM}$(get_resvg_path)${NC}"
            echo ""
            exit 0
        fi
        install_resvg
        echo ""
        ;;
    rsvg-convert)
        print_header
        existing=$(get_rsvg_convert_version)
        if [ -n "$existing" ]; then
            echo -e "  ${GREEN}rsvg-convert is already installed.${NC}"
            echo -e "  ${DIM}${existing}${NC}"
            echo -e "  ${DIM}$(get_rsvg_convert_path)${NC}"
            echo ""
            exit 0
        fi
        install_rsvg_convert
        echo ""
        ;;
    "")
        print_header
        print_status
        echo -e "  ${BOLD}Usage:${NC}"
        echo -e "    ${CYAN}./install.sh inkscape${NC}       Install Inkscape"
        echo -e "    ${CYAN}./install.sh resvg${NC}          Install Resvg"
        echo -e "    ${CYAN}./install.sh rsvg-convert${NC}   Install rsvg-convert (librsvg)"
        echo -e "    ${CYAN}./install.sh --check${NC}        JSON status (for artisan)"
        echo ""
        ;;
    *)
        echo -e "  ${RED}Unknown provider: ${1}${NC}"
        echo -e "  Supported providers: ${CYAN}inkscape${NC}, ${CYAN}resvg${NC}, ${CYAN}rsvg-convert${NC}"
        exit 1
        ;;
esac
