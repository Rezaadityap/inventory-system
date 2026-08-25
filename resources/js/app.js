import "./bootstrap";

import {
    createIcons,
    LayoutGrid,
    Wallet,
    Bell,
    Users,
    ChevronLeft,
    Menu,
    X,
    CircleCheck,
    Pencil,
    Trash,
    AlertTriangle,
    Lock,
    Contact,
    Eye,
    FlaskConical,
    TableOfContents,
    Shield,
    ShieldCheck,
    Database,
    Clock,
    Package,
    LogOut,
    ReceiptText,
    Plus,
    Check,
} from "lucide";

const icons = {
    LayoutGrid,
    Users,
    Wallet,
    Bell,
    ChevronLeft,
    Menu,
    X,
    CircleCheck,
    Pencil,
    Trash,
    AlertTriangle,
    Lock,
    Contact,
    Eye,
    FlaskConical,
    TableOfContents,
    Shield,
    ShieldCheck,
    Database,
    Clock,
    Package,
    LogOut,
    ReceiptText,
    Plus,
    Check,
};

createIcons({ icons });

window.refreshIcons = () => createIcons({ icons });

document.addEventListener("livewire:init", () => {
    Livewire.hook("morph.updated", () => createIcons({ icons }));
});
