import { WebsiteForm } from "../components/website-form"
import { getWebsite } from "../actions"
import { requirePermission } from "@/lib/auth"
import { notFound } from "next/navigation"

interface EditWebsitePageProps {
  params: {
    id: string
  }
}

export async function generateMetadata({ params }: EditWebsitePageProps) {
  const website = await getWebsite(params.id)

  if (!website) {
    return {
      title: "Website không tồn tại - WooCenter",
    }
  }

  return {
    title: `Chỉnh sửa ${website.name} - WooCenter`,
    description: `Chỉnh sửa thông tin website ${website.name}`,
  }
}

export default async function EditWebsitePage({ params }: EditWebsitePageProps) {
  // Kiểm tra quyền truy cập
  await requirePermission("manage_websites")

  // Lấy thông tin website
  const website = await getWebsite(params.id)

  if (!website) {
    notFound()
  }

  return (
    <div className="container mx-auto py-6 space-y-6">
      <h1 className="text-3xl font-bold tracking-tight">Chỉnh sửa Website</h1>
      <WebsiteForm website={website} />
    </div>
  )
}
